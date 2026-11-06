<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class AlbumFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function description($value): Builder
    {
        $this->where('description', 'like', "%$value%");

        return $this->query;
    }

    public function tags($tags): Builder
    {
        return $this->query->byTags($tags);
    }
}
