<?php

namespace App\Filters;

class EmailFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array<string, array<array-key, string>>
     */
    public $relations = [];

    public function sent(mixed $value): void
    {
        $this->nullable('sent', $value);
    }

    public function to(mixed $value): void
    {
        $this->where('to', 'like', "%$value%");
    }

    public function cc(mixed $value): void
    {
        $this->where('cc', 'like', "%$value%");
    }

    public function bcc(mixed $value): void
    {
        $this->where('bcc', 'like', "%$value%");
    }
}
