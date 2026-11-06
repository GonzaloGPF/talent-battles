<?php

namespace App\Traits;

use App\Models\OrderLine;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasOrderLine
{
    public function orderLine(): MorphOne
    {
        return $this->morphOne(OrderLine::class, 'orderable');
    }

    public function isBoughtByMe(): bool
    {
        if (! $this->relationLoaded('orderLine')) {
            return false;
        }

        return (bool) $this->orderLine;
    }
}
