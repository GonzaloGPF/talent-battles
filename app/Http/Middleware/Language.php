<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Language
{
    /**
     * Runs twice. As global middleware, before routing, it gives errors raised before a route is found the language the
     * browser asks for. In the web and api groups, once the session has started, signed-in users get their own locale.
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
