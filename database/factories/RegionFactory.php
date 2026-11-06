<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Region::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'country_id' => associateTo(Country::class),
            'name' => $this->faker->unique()->state(),
            'code' => $this->faker->regexify('[A-Z]{2}'),
        ];
    }
}
