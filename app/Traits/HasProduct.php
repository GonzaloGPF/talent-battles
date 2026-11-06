<?php

namespace App\Traits;

use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasProduct
{
    public function product(): MorphOne
    {
        return $this->morphOne(Product::class, 'orderable');
    }

    public function isInSale(): bool
    {
        return $this->product()->exists();
    }

    public function isPromoted(): bool
    {
        if (! $this->relationLoaded('product')) {
            return false;
        }

        if (empty($this->product?->promoted)) {
            return false;
        }

        return $this->product->promoted->greaterThanOrEqualTo(today());
    }

    public function isExclusiveSold(): bool
    {
        if (! $this->relationLoaded('product')) {
            return true;
        }

        if (empty($this->product)) {
            return false;
        }

        return $this->product->isBlocked();
    }
}
