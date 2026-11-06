<?php

namespace App\Http\Resources;

use App\Models\Activity;
use Illuminate\Http\Request;

/**
 * @mixin Activity
 */
class ActivityResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'receiver_id' => $this->receiver_id,
            'activity_type' => $this->activity_type,
            'created_at' => $this->created_at?->toISOString(),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
            'receiver' => new UserResource($this->whenLoaded('receiver')),
            'target' => $this->morphedResource('target'),
        ];
    }
}
