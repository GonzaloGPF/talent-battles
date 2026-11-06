<?php

use App\Enums\PaymentStates;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->string('guest_id')->nullable()->unique();
            $table->string('payment_state')->default(PaymentStates::PENDING->value);
            $table->string('email')->comment('Email of Buyer User');
            $table->decimal('brute')->default(0);
            $table->decimal('discount')->default(0);
            $table->decimal('discount_amount')->default(0);
            $table->decimal('subtotal')->default(0);
            $table->decimal('tax')->default(0);
            $table->decimal('tax_amount')->default(0);
            $table->decimal('total')->default(0);
            $table->dateTime('succeeded')->nullable();
            $table->json('address')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
