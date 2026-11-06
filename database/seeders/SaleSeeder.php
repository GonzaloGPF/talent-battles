<?php

namespace Database\Seeders;

use App\Models\OrderLine;
use App\Models\Sale;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OrderLine::withoutGlobalScopes()
            ->whereIn('product_type', OrderLine::$CREATION_TYPES)
            ->get()
            ->each(fn (OrderLine $orderLine) => create(Sale::class, [
                'created_by' => $orderLine->seller_id,
                'product_id' => $orderLine->product_id,
            ]));
    }
}
