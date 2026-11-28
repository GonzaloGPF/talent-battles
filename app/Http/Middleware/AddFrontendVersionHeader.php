<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class AddFrontendVersionHeader
{
    public const HEADER = 'X-App-Version';

    /**
     * Tells the frontend which build the server has. The hash of the Vite manifest changes with every build of the
     * frontend, so a page loaded before a deployment sees a different one. There is none without a build or while
     * Vite is running hot.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $version = Vite::manifestHash();

        if ($version) {
            $response->headers->set(self::HEADER, $version);
        }

        return $response;
    }
}
