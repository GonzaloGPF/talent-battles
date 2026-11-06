<?php

namespace Database\Factories;

use App\Interfaces\HasLikesInterface;
use App\Models\Like;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LikeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Like::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $likeableModel = loadModels(HasLikesInterface::class)->random();

        $className = get_class($likeableModel);

        return [
            'created_by' => associateTo(User::class),
            'likeable_type' => $className,
            'likeable_id' => associateTo($className),
        ];
    }
}
