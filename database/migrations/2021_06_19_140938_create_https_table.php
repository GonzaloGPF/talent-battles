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
        Schema::create('https', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('url');
            $table->string('verb');
            $table->boolean('outgoing')->default(true);
            $table->json('data')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('https');
    }
};
