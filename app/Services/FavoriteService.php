<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\FavoriteList;
use Illuminate\Database\Eloquent\Model;

class FavoriteService
{
    public function create(Model $model, string|int|null $favoriteListNameOrId = null): Favorite
    {
        $favoriteList = $this->getFavoriteList($favoriteListNameOrId);

        /** @var Favorite $favorite */
        $favorite = (new Favorite)
            ->fill(['favorite_list_id' => $favoriteList?->id])
            ->favoritable()
            ->associate($model);

        $favorite->save();

        return $favorite;
    }

    private function getFavoriteList(string|int|null $favoriteListNameOrId = null): ?FavoriteList
    {
        if (! $favoriteListNameOrId) {
            return null;
        }

        if (is_numeric($favoriteListNameOrId)) {
            return FavoriteList::find($favoriteListNameOrId);
        } else {
            return FavoriteList::firstOrCreate(['name' => $favoriteListNameOrId]);
        }
    }
}
