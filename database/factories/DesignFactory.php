<?php

namespace Database\Factories;

use App\Enums\AspectRatios;
use App\Enums\DesignTypes;
use App\Enums\Styles;
use App\Enums\Thematics;
use App\Models\Album;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class DesignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by' => associateTo(User::class),
            'album_id' => rand(0, 1) ? associateTo(Album::class) : null,
            'design_type' => randomConstant(DesignTypes::class),
            'style' => rand(0, 1) ? randomConstant(Styles::class) : null,
            'aspect_ratio' => randomConstant(AspectRatios::class),
            'thematic' => randomConstant(Thematics::class),
            'name' => $this->faker->word,
            'color' => $this->faker->hexColor,
            'can_challenge' => $this->faker->boolean,
            'description' => '<p>'.$this->faker->sentence.'</p>',
            'tags' => $this->faker->words(rand(1, 3)),
        ];
    }
}
