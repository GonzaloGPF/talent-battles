<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class ProductFilter extends Filter
{
    public function description(mixed $value): void
    {
        $this->where('description', 'like', "%$value%");
    }

    public function exclusive(mixed $value): void
    {
        $this->boolean('exclusive', $value);
    }

    public function promoted(mixed $value): void
    {
        $value
            ? $this->where('promoted', '>=', today())
            : $this->where(fn (Builder $q) => $q->whereNull('promoted')->orWhere('promoted', '<', today()));
    }
}
