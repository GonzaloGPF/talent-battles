<?php

namespace Database\Seeders;

use App\Interfaces\HasDownloadsInterface;
use App\Models\Download;
use App\Models\Track;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DownloadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([Track::class])
            ->each(fn (string $className) => $this->seed($className));
    }

    private function seed(string $className)
    {
        $users = User::all();

        try {
            /** @var Collection $downloadableModels */
            $downloadableModels = resolve($className)::all();
            $downloadableModels
                ->each(function (HasDownloadsInterface $model) use ($users) {
                    $users->random(rand(1, count($users) - 1))
                        ->each(fn (User $user) => $model->downloads()->save(make(Download::class, [
                            'created_by' => $user->id,
                            'downloable_id' => $model->id,
                            'downloable_type' => get_class($model),
                        ])));
                });
        } catch (Exception $e) {
        }
    }
}
