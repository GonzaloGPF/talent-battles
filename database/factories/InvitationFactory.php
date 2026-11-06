<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invitation>
 */
class InvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $alreadyClaimed = rand(0, 1);

        return [
            'created_by' => associateTo(User::class),
            'user_id' => $alreadyClaimed ? associateTo(User::class) : null,
            'email' => $this->faker->unique()->email(),
            'name' => $alreadyClaimed ? $this->faker->name() : null,
        ];
    }
}
