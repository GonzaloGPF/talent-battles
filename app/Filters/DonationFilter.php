<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class DonationFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function message($value): Builder
    {
        $this->where('message', 'like', "%$value%");

        return $this->query;
    }
}
