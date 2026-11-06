<?php

namespace Database\Factories;

use App\Enums\ActivityTypes;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Activity::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'created_by' => associateTo(User::class),
            'receiver_id' => associateTo(User::class),
            'activity_type' => randomConstant(ActivityTypes::class),
        ];
    }
}
