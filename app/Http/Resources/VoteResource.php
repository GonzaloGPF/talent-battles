<?php

namespace App\Http\Resources;

use App\Models\Vote;
use Illuminate\Http\Request;

/**
 * @mixin Vote
 */
class VoteResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'battle_id' => $this->battle_id,
            'votable_id' => $this->votable_id,
            'value' => $this->value,
            'battle' => new BattleResource($this->whenLoaded('battle')),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
            'votable' => $this->morphedResource('votable'),
        ];
    }
}
