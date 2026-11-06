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
        Schema::create('order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('seller_id')->nullable()->constrained('users');
            $table->foreignId('order_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->nullableMorphs('orderable');
            $table->string('product_type')->nullable();
            $table->string('description');
            $table->decimal('price')->default(0);
            $table->decimal('quantity')->default(1);
            $table->decimal('subtotal')->default(0);
            $table->decimal('discount')->default(0);
            $table->decimal('discount_amount')->default(0);
            $table->decimal('total')->default(0);
            $table->decimal('tax')->default(0);
            $table->decimal('tax_amount')->default(0);
            $table->decimal('total_with_taxes')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_lines');
    }
};
