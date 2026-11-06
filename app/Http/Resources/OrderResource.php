<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * @mixin Order
 */
class OrderResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'payment_state' => $this->payment_state,
            'succeeded' => $this->succeeded?->toISOString(),
            'brute' => $this->brute,
            'discount' => $this->discount,
            'discount_amount' => $this->discount_amount,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toISOString(),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
            'orderLines' => OrderLineResource::collection($this->whenLoaded('orderLines')),
            $this->addCount('orderLines'),
        ];
    }
}
