<?php

namespace App\Services;

use App\Models\User;

/**
 * Signs users in on the web guard, which Sanctum also uses for the SPA. The guard renews the session id and the CSRF
 * token on every sign-in, so an id someone saw or planted before is worthless afterwards.
 */
class AuthService
{
    public function login(User $user): void
    {
        auth('web')->login($user);
    }

    /**
     * @param  array{email: string, password: string}  $credentials
     */
    public function attempt(array $credentials): bool
    {
        return auth('web')->attempt($credentials);
    }
}
