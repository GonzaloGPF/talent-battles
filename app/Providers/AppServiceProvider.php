<?php

namespace App\Providers;

use App\Api\StripeApi;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use LaRecipe\Domains\Markdown\Components\RenderedComponent;
use LaRecipe\Facades\LaRecipe;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ImageManager::class, fn () => ImageManager::usingDriver(Driver::class));

        Str::macro('removeSpecialChars', fn (string $value) => removeSpecialChars($value));

        Stringable::macro('removeSpecialChars', fn (?bool $removeDots = false) => new Stringable(removeSpecialChars($this->value, $removeDots)));

        //        if (app()->isProduction()) {
        //            Vite::useBuildDirectory('storage/build');
        //        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('payment_intent', fn (string $value) => resolve(StripeApi::class)->retrievePaymentIntent($value));

        $this->configureRateLimiting();

        $this->registerDocsComponents();
    }

    /**
     * Configure the rate limiters for the application.
     */
    private function configureRateLimiting(): void
    {
        /*
         * A page of the SPA makes up to 13 API requests when it loads from scratch: 300 a minute leaves room for fast
         * browsing and stops a script hammering the API.
         */
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(300)->by($request->user()?->id ?: $request->ip());
        });

        /*
         * By email and IP: one user cannot lock out another by guessing their email, nor try many passwords for one.
         */
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(Str::lower($request->string('email')).'|'.$request->ip());
        });

        /*
         * Endpoints that send emails or take a password or a token: registering, resetting or changing a password,
         * and the contact form.
         */
        RateLimiter::for('sensitive', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Components the docs can use. <LegalOwner /> shows who runs the site, whose personal data live in the .env
     * instead of the repository.
     */
    private function registerDocsComponents(): void
    {
        LaRecipe::component('LegalOwner')
            ->description('Who runs the site: owner, trade name, NIF, address and email')
            ->fallback(fn (array $props, string $children, RenderedComponent $page) => view('docs.legal_owner', [
                'rows' => $this->legalOwnerRows($page->version),
            ]))
            ->markdown(fn (array $props, string $children, RenderedComponent $page) => collect($this->legalOwnerRows($page->version))
                ->map(fn (string $value, string $label) => "- **{$label}:** {$value}")
                ->implode("\n"));
    }

    /**
     * The docs use their versions (es, en) as languages. Data the .env does not set are left out.
     *
     * @return array<string, string> label => value
     */
    private function legalOwnerRows(?string $locale): array
    {
        return array_filter([
            trans('legal.owner', locale: $locale) => (string) config('company.owner.name'),
            trans('legal.commercial_name', locale: $locale) => (string) config('company.commercial_name'),
            trans('legal.nif', locale: $locale) => (string) config('company.owner.nif'),
            trans('legal.address', locale: $locale) => (string) config('company.owner.address'),
            trans('legal.email', locale: $locale) => (string) config('company.owner.email'),
        ]);
    }
}
