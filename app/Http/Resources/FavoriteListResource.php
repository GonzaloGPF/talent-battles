<?php

namespace App\Http\Resources;

use App\Models\FavoriteList;
use Illuminate\Http\Request;

/**
 * @mixin FavoriteList
 */
class FavoriteListResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'name' => $this->name,
            'updated_at' => $this->updated_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
            $this->addCount('favorites'),
        ];
    }
}
