<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Accounts created through OAuth got a password from uniqid(), which follows the clock: knowing when the account was
 * created was enough to guess it. Every account linked to an OAuth provider gets a random password and loses its
 * "remember me" token. Their owners keep signing in through the provider, or set a password with "forgot password".
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('oauth_driver')
            ->lazyById()
            ->each(fn (object $user) => DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'password' => Hash::make(Str::random(64)),
                    'remember_token' => Str::random(60),
                ]));
    }

    /**
     * The old passwords cannot come back, and they must not.
     */
    public function down(): void
    {
        //
    }
};
