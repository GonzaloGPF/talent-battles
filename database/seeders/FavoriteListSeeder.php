<?php

namespace Database\Seeders;

use App\Models\FavoriteList;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::all()
            ->each(fn (User $user) => create(FavoriteList::class, ['created_by' => $user->id]));
    }
}
