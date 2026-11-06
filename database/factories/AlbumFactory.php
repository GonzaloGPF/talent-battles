<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlbumFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Album::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'created_by' => associateTo(User::class),
            'name' => $this->faker->words(rand(1, 5), true),
            'released' => $this->faker->dateTimeBetween('-1 year', 'today'),
            'description' => $this->faker->text,
            'tags' => $this->faker->words(rand(1, 3)),
        ];
    }
}
