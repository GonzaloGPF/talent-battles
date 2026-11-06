<?php

use App\Enums\Gender;
use App\Enums\Levels;
use App\Enums\Roles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('role')->default(Roles::USER->value);
            $table->string('level')->default(Levels::NOVICE->value);
            $table->string('name');
            $table->string('last_name')->nullable();
            $table->string('nick')->nullable();
            $table->string('email')->unique();
            $table->date('birthdate')->nullable();
            $table->enum('gender', [Gender::MALE->value, Gender::FEMALE->value])->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('token')->nullable();
            $table->string('oauth_id')->nullable();
            $table->string('oauth_driver')->nullable();
            $table->rememberToken();
            $table->string('locale')->default(config('app.locale'));
            $table->string('currency')->default(config('company.currency'));
            $table->decimal('points')->default(0);
            $table->integer('experience')->default(0);
            $table->float('rating')->default(0);
            $table->string('quote')->nullable();
            $table->text('bio')->nullable();
            $table->json('skills')->nullable();
            $table->json('categories')->nullable();
            $table->json('address')->nullable();
            $table->json('account')->nullable();
            $table->json('social_settings')->nullable();
            $table->json('notification_settings')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_donation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
