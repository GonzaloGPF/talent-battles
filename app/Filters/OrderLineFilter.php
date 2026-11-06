<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class OrderLineFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [
        //        'seller' => [
        //            'seller' => 'ids'
        //        ]
    ];

    public function seller($value): Builder
    {
        return $this->whereIn('seller_id', $value);
    }
}
