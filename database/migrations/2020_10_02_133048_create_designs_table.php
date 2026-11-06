<?php

use App\Models\Design;
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
        Schema::create('designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->string('design_type')->nullable();
            $table->string('thematic')->nullable();
            $table->string('style')->nullable();
            $table->string('aspect_ratio')->nullable();
            $table->foreignId('album_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('color')->nullable()->default(Design::DEFAULT_COLOR);
            $table->text('description')->nullable();
            $table->boolean('can_challenge')->nullable();
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
        Schema::dropIfExists('designs');
    }
};
