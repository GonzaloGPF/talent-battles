<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::all()
            ->each(function (User $user) {
                create(Notification::class, [
                    'notifiable_id' => $user->id,
                    'notifiable_type' => User::class,
                ], rand(1, 5));
            });
    }
}
