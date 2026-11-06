<?php

namespace App\Providers;

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
        //
    }
}
