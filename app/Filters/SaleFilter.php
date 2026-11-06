<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class SaleFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function name($name): Builder
    {
        $this->whereHas('product', function (Builder $builder) use ($name) {
            return $builder->where('name', 'like', "%$name%");
        });

        return $this->query;
    }
}
