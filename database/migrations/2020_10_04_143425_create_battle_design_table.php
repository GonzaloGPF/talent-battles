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
        Schema::create('battle_design', function (Blueprint $table) {
            $table->foreignId('battle_id')->constrained()->onDelete('cascade');
            $table->foreignId('design_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->primary(['battle_id', 'design_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battle_design');
    }
};
