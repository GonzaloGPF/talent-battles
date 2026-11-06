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
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->morphs('downloable');
            $table->timestamps();

            $table->unique(['created_by', 'downloable_id', 'downloable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downloads');
    }
};
