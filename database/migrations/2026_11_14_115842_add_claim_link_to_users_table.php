<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The link to claim a profile carries a key; the account keeps its hash and when it expires.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('claim_token', 64)->nullable()->after('token');
            $table->timestamp('claim_expires_at')->nullable()->after('claim_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['claim_token', 'claim_expires_at']);
        });
    }
};
