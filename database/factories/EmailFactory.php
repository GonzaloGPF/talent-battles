<?php

namespace Database\Factories;

use App\Enums\EmailTypes;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class EmailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $emailType = randomConstant(EmailTypes::class);

        return [
            'created_by' => associateTo(User::class),
            'email_type' => $emailType,
            'to' => [$this->faker->email()],
            'cc' => rand(0, 1) ? [$this->faker->email()] : null,
            'bcc' => rand(0, 1) ? [$this->faker->email()] : null,
            'sent' => rand(0, 1) ? $this->faker->dateTimeBetween('-3 months', 'today') : null,
            'meta' => $this->getMeta($emailType),
        ];
    }

    private function getMeta(string $emailType): array
    {
        return match ($emailType) {
            EmailTypes::VERIFY_EMAIL->value => [
                'user_name' => $this->faker->name(),
                'email' => $this->faker->email(),
                'url' => route('users.verify', ['user' => $this->faker->regexify('[A-Za-z0-9]{20}')]),
            ],
            EmailTypes::RESET_PASSWORD->value => [
                'user_name' => $this->faker->name(),
                'email' => $this->faker->email(),
                'url' => route('auth.password.reset', ['token' => $this->faker->regexify('[A-Za-z0-9]{20}')]),
            ],
            EmailTypes::ORDER_SUCCESS->value => [
                'order_id' => associateTo(Order::class),
                'user_name' => $this->faker->name(),
                'lines' => [
                    [
                        'description' => $this->faker->sentence(),
                        'url' => 'some/file/url',
                    ],
                ],
            ],
            EmailTypes::CREATION_SOLD->value => [
                'user_name' => $this->faker->name(),
                'lines' => [
                    [
                        'description' => $this->faker->sentence(),
                        'amount' => $this->faker->numberBetween(rand(5, 500)),
                        'commission_amount' => $this->faker->numberBetween(rand(5, 10)),
                        'total' => $this->faker->numberBetween(rand(5, 500)),
                    ],
                ],
            ],
            EmailTypes::CONTACT_EMAIL->value => [
                'email' => $this->faker->email(),
                'message' => $this->faker->text(),
            ],
            EmailTypes::INVITATION_EMAIL->value => [
                'creator_name' => $this->faker->name(),
                'user_name' => $this->faker->name(),
            ],
        };
    }
}
