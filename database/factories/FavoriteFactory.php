<?php

namespace Database\Factories;

use App\Interfaces\HasFavoritesInterface;
use App\Models\Favorite;
use App\Models\FavoriteList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Favorite::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $favoritableModel = loadModels(HasFavoritesInterface::class)->random();

        $className = get_class($favoritableModel);

        $userId = associateTo(User::class);

        return [
            'created_by' => $userId,
            'favorite_list_id' => associateTo(FavoriteList::class, ['created_by' => $userId], true),
            'favoritable_type' => $className,
            'favoritable_id' => associateTo($className),
        ];
    }
}
