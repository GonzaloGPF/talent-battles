<?php

namespace App\Http\Resources;

use App\Models\Sale;
use Illuminate\Http\Request;

/**
 * @mixin Sale
 */
class SaleResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'product_id' => $this->product_id,
            'description' => $this->description,
            'amount' => $this->amount,
            'commission' => $this->commission,
            'commission_amount' => $this->commission_amount,
            'total' => $this->total,
            'created_at' => $this->created_at?->toISOString(),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
