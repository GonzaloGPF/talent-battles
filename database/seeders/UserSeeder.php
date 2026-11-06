<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()
            ->admin()
            ->create();

        User::factory()
            ->create([
                'role' => Roles::USER,
                'name' => 'Tester User',
                'email' => 'tester@email.com',
            ]);

        User::factory()
            ->count(20)
            ->create([
                'role' => Roles::USER,
            ]);
    }
}
