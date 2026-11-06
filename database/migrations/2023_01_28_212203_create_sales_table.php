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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('product_id')->constrained();
            $table->string('description');
            $table->decimal('amount')->default(0);
            $table->decimal('commission')->default(0);
            $table->decimal('commission_amount')->default(0);
            $table->decimal('total')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
