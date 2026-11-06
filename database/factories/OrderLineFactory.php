<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderLineFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderLine::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 5, 100);
        $quantity = 1;
        $subtotal = $price * $quantity;
        $discount = 0;
        $discountAmount = $subtotal * ($discount / 100);
        $total = $subtotal - $discount;
        $tax = 0;
        $taxAmount = $total * ($tax / 100);
        $totalWithTaxes = $total + $taxAmount;

        return [
            'created_by' => associateTo(User::class),
            'seller_id' => associateTo(User::class),
            'order_id' => associateTo(Order::class),
            'product_id' => associateTo(Product::class),
            'description' => $this->faker->sentence,
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'tax' => $tax,
            'tax_amount' => $taxAmount,
            'total_with_taxes' => $totalWithTaxes,
        ];
    }
}
