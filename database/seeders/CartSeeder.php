<?php

namespace Database\Seeders;

use App\Enums\ProductTypes;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::whereIn('product_type', [ProductTypes::TRACK, ProductTypes::DESIGN])->get();

        User::get('id')
            ->each(fn (User $user) => $this->createCart($user, $products));
    }

    private function createCart(User $user, Collection $products): ?Cart
    {
        if (rand(0, 10) > 8) {
            return null;
        }

        return create(Cart::class, [
            'created_by' => $user->id,
            'items' => $products
                ->random(rand(1, 3))
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'quantity' => 1,
                    //                    'orderable_type' => $product->orderable_type,
                    //                    'orderable_id' => $product->orderable_id,
                ])->toArray(),
        ]);
    }
}
