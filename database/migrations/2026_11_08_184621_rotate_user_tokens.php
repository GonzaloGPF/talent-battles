<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Tokens used to come from uniqid(), which follows the clock: knowing when a user signed up was enough to guess them.
 * Every pending token gets a random one. Links already sent stop working, and new ones can be asked for.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('token')
            ->lazyById()
            ->each(fn (object $user) => DB::table('users')
                ->where('id', $user->id)
                ->update(['token' => Str::random(64)]));
    }

    /**
     * The old tokens cannot come back, and they must not.
     */
    public function down(): void
    {
        //
    }
};
