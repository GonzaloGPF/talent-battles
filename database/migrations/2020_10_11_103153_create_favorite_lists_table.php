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
        Schema::create('favorite_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->string('name');
            $table->timestamps();
            $table->unique(['created_by', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorite_lists');
    }
};
