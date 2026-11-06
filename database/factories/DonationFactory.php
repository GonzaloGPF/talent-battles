<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'created_by' => rand(1, 10) > 4 ? associateTo(User::class) : null,
            'amount' => $this->faker->randomFloat(2, 1, 500),
            'message' => $this->faker->sentence(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'today'),
        ];
    }
}
