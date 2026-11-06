<?php

namespace Database\Factories;

use App\Enums\Genres;
use App\Enums\Instruments;
use App\Enums\Keys;
use App\Enums\Thematics;
use App\Enums\TrackTypes;
use App\Models\Album;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrackFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Track::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'created_by' => associateTo(User::class),
            'album_id' => rand(0, 1) ? associateTo(Album::class) : null,
            'track_type' => randomConstant(TrackTypes::class),
            'thematic' => randomConstant(Thematics::class),
            'key' => randomConstant(Keys::class),
            'instruments' => collect(randomConstant(Instruments::class, true))->take(rand(1, 5))->toArray(),
            'genres' => collect(randomConstant(Genres::class, true))->take(rand(1, 3))->toArray(),
            'name' => $this->faker->word,
            'color' => $this->faker->hexColor,
            'bpm' => $this->faker->randomFloat(2, 50, 180),
            'can_challenge' => $this->faker->boolean,
            'duration' => 0, // $this->faker->randomFloat(2, 30000, 360000),
            'description' => '<p>'.$this->faker->sentence.'</p>',
            'tags' => $this->faker->words(rand(1, 3)),
        ];
    }
}
