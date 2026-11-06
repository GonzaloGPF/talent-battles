<?php

namespace Database\Seeders;

use App\Interfaces\HasFavoritesInterface;
use App\Models\Album;
use App\Models\Design;
use App\Models\Favorite;
use App\Models\Track;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class FavoriteSeeder extends Seeder
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
        $users = User::select(['id'])->with(['favoriteLists'])->get();

        try {
            /** @var Collection $favoritableModels */
            $favoritableModels = resolve($className)::all();
            $favoritableModels
                ->each(function (HasFavoritesInterface $model) use ($users) {
                    $users->random(rand(1, count($users) - 1))
                        ->each(fn (User $user) => $model->favorites()->save(make(Favorite::class, [
                            'created_by' => $user->id,
                            'favorite_list_id' => $user->favoriteLists->random()?->id,
                            'favoritable_id' => $model->id,
                            'favoritable_type' => get_class($model),
                        ])));
                });
        } catch (Exception $e) {
        }
    }
}
