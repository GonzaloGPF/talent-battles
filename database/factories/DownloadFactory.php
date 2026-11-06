<?php

namespace Database\Factories;

use App\Interfaces\HasDownloadsInterface;
use App\Models\Download;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DownloadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Download::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $downloableModel = loadModels(HasDownloadsInterface::class)->random();

        $className = get_class($downloableModel);

        return [
            'created_by' => associateTo(User::class),
            'downloable_type' => $className,
            'downloable_id' => associateTo($className),
        ];
    }
}
