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

    public function promoted($value): Builder
    {
        $value
            ? $this->where('promoted', '>=', today())
            : $this->where(fn (Builder $q) => $q->whereNull('promoted')->orWhere('promoted', '<', today()));

        return $this->query;
    }
}
