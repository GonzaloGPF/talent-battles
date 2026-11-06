<?php

namespace App\Http\Resources;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Invitation
 */
class InvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'user_id' => $this->user_id,
            'email' => $this->when(auth()->id() === $this->created_by, $this->email),
            'name' => $this->name,
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
            'has_account' => $this->when($this->relationLoaded('user'), fn () => (bool) $this->user?->account),
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toDateString(),
        ];
    }
}
