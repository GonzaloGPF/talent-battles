<?php

namespace App\Filters;

class OrderLineFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array<string, array<array-key, string>>
     */
    public $relations = [
        //        'seller' => [
        //            'seller' => 'ids'
        //        ]
    ];

    public function seller(mixed $value): void
    {
        $this->whereIn('seller_id', $value);
    }
}
