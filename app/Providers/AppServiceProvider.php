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

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ImageManager::class, fn () => ImageManager::usingDriver(Driver::class));

        Str::macro('removeSpecialChars', fn (string $value) => removeSpecialChars($value));

        Stringable::macro('removeSpecialChars', fn (?bool $removeDots = false) => new static(removeSpecialChars($this->value, $removeDots)));

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
}
