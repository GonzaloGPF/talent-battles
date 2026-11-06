<?php

namespace Database\Seeders;

use App\Models\Design;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

class DesignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::with(['albums' => fn (HasMany $builder) => $builder->select(['id', 'created_by'])])->get();

        Design::factory()
            ->count(20)
            ->state(function () use ($users) {
                $user = $users->random();
                try {
                    $albumId = $user->albums?->random()?->id;
                } catch (InvalidArgumentException $exception) {
                    $albumId = null;
                }

                return [
                    'created_by' => $user->id,
                    'album_id' => $albumId,
                ];
            })
            ->create();
    }
}
