<?php

namespace App\Filters;

class OrderFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array<string, array<array-key, string>>
     */
    public $relations = [];

    public function succeeded(mixed $value): void
    {
        $this->nullable('succeeded', $value);
    }
}
