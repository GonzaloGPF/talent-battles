<?php

namespace App\Http\Resources;

use App\Models\Favorite;
use Illuminate\Http\Request;

/**
 * @mixin Favorite
 */
class FavoriteResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'type' => class_basename($this->favoritable_type),
            'favorite_list_id' => $this->favorite_list_id,
            'created_at' => $this->created_at?->toISOString(),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
            'favoriteList' => new FavoriteListResource($this->whenLoaded('favoriteList')),
            'favoritable' => $this->morphedResource('favoritable'),
        ];
    }
}
