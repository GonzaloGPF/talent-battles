<?php

namespace App\Http\Resources;

use App\Models\Battle;
use Illuminate\Http\Request;

/**
 * @mixin Battle
 */
class BattleResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'won_by' => $this->won_by,
            'winner_id' => $this->winner_id,
            'winner_type' => $this->winner_type,
            'battle_state' => $this->battle_state,
            'battle_type' => $this->battle_type,
            'created_at' => $this->created_at?->toISOString(),
            'ending' => $this->created_at?->addDays(config('company.battle_ttl')),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
            'wonBy' => new UserResource($this->whenLoaded('wonBy')),
            'votes' => VoteResource::collection($this->whenLoaded('votes')),
            'tracks' => $this->when($this->isTrack(), TrackResource::collection($this->whenLoaded('tracks'))),
            'designs' => $this->when($this->isDesign(), DesignResource::collection($this->whenLoaded('designs'))),
            'winner' => $this->morphedResource('winner'),
            $this->addCount('votes'),
        ];
    }
}
