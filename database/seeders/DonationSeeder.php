<?php

namespace Database\Seeders;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Database\Seeder;

class DonationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        make(Donation::class, [], 50)
            ->each(fn (Donation $donation) => $donation->forceFill([
                'created_by' => rand(1, 10) > 3 ? $users->random()->id : null,
            ])->save());
    }
}
