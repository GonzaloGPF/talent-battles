<?php

namespace Database\Factories;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Models\Battle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BattleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Battle::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'created_by' => associateTo(User::class),
            'battle_state' => randomConstant(BattleStates::class),
            'battle_type' => randomConstant(BattleTypes::class),
        ];
    }
}
