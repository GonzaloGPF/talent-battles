<?php

namespace Database\Seeders;

use App\Interfaces\HasLikesInterface;
use App\Models\Album;
use App\Models\Like;
use App\Models\Track;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class LikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([User::class, Album::class, Track::class])
            ->each(fn (string $className) => $this->seed($className));
    }

    private function seed(string $className)
    {
        $users = User::all();

        try {
            /** @var Collection $likeableModels */
            $likeableModels = resolve($className)::all();
            $likeableModels
                ->each(function (HasLikesInterface $model) use ($users) {
                    $users->random(rand(1, count($users) - 1))
                        ->each(fn (User $user) => $model->likes()->save(make(Like::class, [
                            'created_by' => $user->id,
                            'likeable_id' => $model->id,
                            'likeable_type' => get_class($model),
                        ])));
                });
        } catch (Exception $e) {
        }
    }
}
