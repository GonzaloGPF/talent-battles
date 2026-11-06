<?php

use App\Models\Track;
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
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('album_id')->nullable()->constrained()->onDelete('set null');
            $table->string('track_type');
            $table->string('thematic')->nullable();
            $table->string('key')->nullable();
            $table->string('name');
            $table->string('color')->nullable()->default(Track::DEFAULT_COLOR);
            $table->decimal('bpm')->nullable();
            $table->decimal('duration')->default(0)->comment('In seconds');
            $table->text('description')->nullable();
            $table->boolean('can_challenge')->nullable();
            $table->json('instruments')->nullable();
            $table->json('genres')->nullable();
            $table->json('waveform')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
