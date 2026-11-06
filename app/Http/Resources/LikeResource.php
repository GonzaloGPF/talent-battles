<?php

namespace App\Http\Resources;

use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Like
 */
class LikeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toISOString(),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
        ];
    }
}
