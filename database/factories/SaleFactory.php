<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->numberBetween(5, 300);
        $commission = config('company.fees.creation');
        $commissionAmount = $amount * ($commission / 100);

        return [
            'created_by' => associateTo(User::class),
            'product_id' => associateTo(Product::class),
            'description' => $this->faker->sentence,
            'amount' => $amount,
            'commission' => $commission,
            'commission_amount' => $commissionAmount,
            'total' => $amount - $commissionAmount,
        ];
    }
}
