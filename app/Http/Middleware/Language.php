<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Language
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            app()->setLocale(auth()->user()->locale ?? config('app.fallback_locale'));
        } else {
            app()->setLocale($request->getPreferredLanguage(['es', 'en']));
        }

        return $next($request);
    }
}
