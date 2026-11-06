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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->nullableMorphs('orderable');
            $table->string('product_type');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price')->default(0);
            $table->boolean('exclusive')->nullable();
            $table->integer('sales')->default(0);
            $table->dateTime('last_sale')->nullable();
            $table->dateTime('promoted')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
