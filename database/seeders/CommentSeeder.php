<?php

namespace Database\Seeders;

use App\Interfaces\HasCommentsInterface;
use App\Models\Album;
use App\Models\Comment;
use App\Models\Track;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([User::class, Album::class, Track::class, Design::class])
            ->each(fn (string $className) => $this->seed($className));
    }

    private function seed(string $className)
    {
        $users = User::all();

        try {
            /** @var Collection $commentableModels */
            $commentableModels = resolve($className)::all();
            $commentableModels
                ->each(function (HasCommentsInterface $model) use ($users) {
                    $users->random(rand(1, count($users) - 1))
                        ->each(fn (User $user) => $model->comments()->save(make(Comment::class, [
                            'created_by' => $user->id,
                            'commentable_id' => $model->id,
                            'commentable_type' => get_class($model),
                        ])));
                });
        } catch (Exception $e) {
        }
    }
}
