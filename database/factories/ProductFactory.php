<?php

namespace Database\Factories;

use App\Enums\ProductTypes;
use App\Interfaces\HasProductInterface;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasProductModel = loadModels(HasProductInterface::class)->random();

        $className = get_class($hasProductModel);

        $hasSales = rand(1, 10) > 8;

        return [
            'created_by' => associateTo(User::class),
            'product_type' => randomConstant(ProductTypes::class),
            'orderable_type' => $className,
            'orderable_id' => associateTo($className),
            'name' => $this->faker->words(rand(1, 3), true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 300, 5),
            'exclusive' => $hasSales ? false : $this->faker->boolean,
            'sales' => $hasSales ? rand(1, 3) : 0,
            'last_sale' => $hasSales ? $this->faker->dateTimeBetween('-3 months', 'today') : null,
            'promoted' => rand(1, 3) === 3 ? $this->faker->dateTimeBetween('-3 months', '+3 months') : null,
        ];
    }
}
