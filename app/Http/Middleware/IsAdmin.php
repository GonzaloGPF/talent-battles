<?php

namespace App\Http\Middleware;

use App\Enums\Roles;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @throws AuthorizationException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (! hasRole(Roles::ADMIN)) {
            throw new AuthorizationException;
        }

        return $next($request);
    }
}
