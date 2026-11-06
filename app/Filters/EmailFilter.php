<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class EmailFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function sent($value): Builder
    {
        $this->nullable('sent', $value);

        return $this->query;
    }

    public function to($value): Builder
    {
        $this->where('to', 'like', "%$value%");

        return $this->query;
    }

    public function cc($value): Builder
    {
        $this->where('cc', 'like', "%$value%");

        return $this->query;
    }

    public function bcc($value): Builder
    {
        $this->where('bcc', 'like', "%$value%");

        return $this->query;
    }
}
