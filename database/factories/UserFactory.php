<?php

namespace Database\Factories;

use App\Enums\Categories;
use App\Enums\Gender;
use App\Enums\Roles;
use App\Enums\Skills;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $name = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $fullName = "$name $lastName";
        $domain = $this->faker->safeEmailDomain();
        $experience = $this->faker->numberBetween(1, 1000);

        return [
            'name' => $name,
            'nick' => rand(0, 10) > 4 ? $this->faker->userName() : null,
            'last_name' => $lastName,
            'role' => randomConstant(Roles::class),
            'level' => resolve(UserService::class)
                ->getLevel($experience),
            'email' => Str::of($fullName)
                ->lower()
                ->replace(' ', '.')
                ->append("@$domain")
                ->removeSpecialChars()
                ->value(),
            'birthdate' => $this->faker->dateTimeBetween('-50 years', '-16 years'),
            'gender' => randomConstant(Gender::class),
            'verified_at' => rand(0, 1) ? $this->faker->dateTimeBetween('-1 year', 'today') : null,
            'password' => 'Secret123**',
            'phone' => $this->faker->phoneNumber,
            'remember_token' => Str::random(10),
            'points' => rand(1, 4) > 3 ? $this->faker->numberBetween(0, 500) : 0,
            'experience' => $experience,
            'rating' => rand(0, 1) ? $this->faker->numberBetween(0, 100) : 0,
            'quote' => $this->faker->sentence,
            'bio' => $this->faker->text,
            'skills' => randomConstant(Skills::class, true),
            'categories' => randomConstant(Categories::class, true),
            'address' => [
                'country' => $this->faker->country(),
                'country_code' => $this->faker->countryCode(),
                'region' => $this->faker->state(),
                'city' => $this->faker->city(),
                'postcode' => $this->faker->postcode(),
                'line1' => $this->faker->streetAddress(),
                //                'line2' => '',
            ],
            'social_settings' => [
                'youtube' => rand(1, 10) > 5 ? $this->faker->url : null,
                'twitter' => rand(1, 10) > 5 ? $this->faker->url : null,
                'soundcloud' => rand(1, 10) > 5 ? $this->faker->url : null,
                'facebook' => rand(1, 10) > 5 ? $this->faker->url : null,
                'instagram' => rand(1, 10) > 5 ? $this->faker->url : null,
            ],
        ];
    }

    public function admin(): UserFactory
    {
        return $this->state([
            'name' => 'Admin',
            'email' => 'admin@email.com',
            'role' => Roles::ADMIN,
            'verified_at' => now(),
        ]);
    }

    public function madrid(): UserFactory
    {
        return $this->state([
            'name' => 'Stripe User',
            'email' => 'stripe@email.com',
            'address' => [
                'region' => 'Madrid',
                'city' => 'Madrid',
                'postcode' => '28100',
                'line1' => 'Some Line1',
                'line2' => 'Some Line2',
                'country' => 'Spain',
                'country_code' => 'es',
            ],
        ]);
    }
}
