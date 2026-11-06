<?php

namespace Database\Factories;

use App\Interfaces\HasVotesInterface;
use App\Models\Battle;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vote::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $votableModel = loadModels(HasVotesInterface::class)->random();

        $className = get_class($votableModel);

        return [
            'created_by' => associateTo(User::class),
            'battle_id' => associateTo(Battle::class),
            'votable_id' => associateTo($className),
            'votable_type' => $className,
            'value' => rand(1, 5),
        ];
    }
}
