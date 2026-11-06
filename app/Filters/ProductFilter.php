<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class ProductFilter extends Filter
{
    public function description($value): Builder
    {
        $this->where('description', 'like', "%$value%");

        return $this->query;
    }

    public function exclusive($value): Builder
    {
        $this->boolean('exclusive', $value);

        return $this->query;
    }

    /**
     * @return TrackFilter
     */
    public function promoted($value): Filter
    {
        if (! $value) {
            return $this->where(function (Builder $q) {
                return $q->whereNull('promoted')
                    ->orWhere('promoted', '<', today());
            });
        }

        return $this->where('promoted', '>=', today());
    }
}
