<?php

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
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->string('email_type');
            $table->json('to');
            $table->json('cc')->nullable()->comment('Carbon Copy');
            $table->json('bcc')->nullable()->comment('Blind Carbon Copy');
            $table->dateTime('sent')->nullable();
            $table->integer('retries')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
