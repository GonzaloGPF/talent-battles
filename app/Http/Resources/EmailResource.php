<?php

namespace App\Http\Resources;

use App\Enums\Roles;
use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Email
 */
class EmailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'email_type' => $this->email_type,
            'to' => $this->to,
            'cc' => $this->when(hasRole(Roles::ADMIN), $this->cc),
            'bcc' => $this->when(hasRole(Roles::ADMIN), $this->bcc),
            'sent' => $this->sent?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
        ];
    }
}
