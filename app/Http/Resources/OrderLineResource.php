<?php

namespace App\Http\Resources;

use App\Models\OrderLine;
use Illuminate\Http\Request;

/**
 * @mixin OrderLine
 */
class OrderLineResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_type' => $this->product_type,
            'created_by' => $this->created_by,
            'seller_id' => $this->seller_id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'orderable_id' => $this->orderable_id,
            'orderable_type' => $this->orderable_type,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'discount_amount' => $this->discount_amount,
            'tax' => $this->tax,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'total_with_taxes' => $this->total_with_taxes,
            'order' => new OrderResource($this->whenLoaded('order')),
            'product' => new OrderResource($this->whenLoaded('product')),
            'seller' => new UserResource($this->whenLoaded('seller')),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
            'orderable' => $this->morphedResource('orderable'),
            'created_at' => $this->created_at?->toISOString(),
            $this->mergeWhen(auth()->hasUser() && $this->created_by === auth()->id(), [
                'file' => new FileResource($this->whenLoaded('file')),
            ]),
        ];
    }
}
