<?php

namespace App\Traits;

use App\Enums\PaymentStates;
use App\Models\OrderLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasOrderLine
{
    /**
     * The paid purchase of the creation by the signed-in user: order lines only show to their buyer (ViewOnlyMine),
     * and an order whose payment is pending or failed is not a purchase yet.
     *
     * @return MorphOne<OrderLine, $this>
     */
    public function orderLine(): MorphOne
    {
        return $this->morphOne(OrderLine::class, 'orderable')
            ->whereHas('order', fn (Builder $query) => $query->where('payment_state', PaymentStates::SUCCESS))
            ->latest();
    }

    public function isBoughtByMe(): bool
    {
        if (! $this->relationLoaded('orderLine')) {
            return false;
        }

        return (bool) $this->orderLine;
    }
}
