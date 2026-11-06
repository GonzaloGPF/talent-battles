<?php

namespace Database\Factories;

use App\Enums\PaymentStates;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'created_by' => associateTo(User::class),
            'payment_state' => randomConstant(PaymentStates::class),
            'email' => $this->faker->email(),
            'address' => [
                'country' => $this->faker->country(),
                'country_code' => $this->faker->countryCode(),
                'region' => $this->faker->state(),
                'city' => $this->faker->city(),
                'postcode' => $this->faker->postcode(),
                'line1' => $this->faker->streetAddress(),
                //                'line2' => '',
            ],
        ];
    }
}
