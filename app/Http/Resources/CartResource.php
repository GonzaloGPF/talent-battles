<?php

namespace App\Http\Resources;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Cart
 */
class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'guest_id' => $this->guest_id,
            'created_by' => $this->created_by,
            'items' => $this->items->toArray(),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
        ];
    }
}
