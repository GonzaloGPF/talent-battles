<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;

/**
 * @mixin Product
 */
class ProductResource extends ApiResource
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
            'orderable_id' => $this->orderable_id,
            'orderable_type' => $this->orderable_type,
            'type' => class_basename($this->orderable_type),
            'product_type' => $this->product_type,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'exclusive' => $this->exclusive,
            'sales' => $this->sales,
            'last_sale' => $this->last_sale?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
            'orderable' => $this->morphedResource('orderable'),
            'file' => $this->when(auth()->id() === $this->created_by, new FileResource($this->whenLoaded('file'))),
        ];
    }
}
