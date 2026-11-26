<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A sale keeps the order line it comes from, whose payment funds the transfer to the seller, and the id of that
     * Stripe transfer once it is made.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('order_line_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->string('transfer_id')->nullable()->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_line_id');
            $table->dropColumn('transfer_id');
        });
    }
};
