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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('receiver_id')->nullable()->constrained('users'); // when donating, there are not any receiver
            $table->string('activity_type');
            $table->morphs('target');
            $table->timestamps();

            $table->unique(['target_id', 'target_type', 'created_by', 'activity_type'], 'unique_activities_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
