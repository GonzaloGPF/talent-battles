<?php

namespace App\Filters;

class AlbumFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array<string, array<array-key, string>>
     */
    public $relations = [];

    public function description(mixed $value): void
    {
        $this->where('description', 'like', "%$value%");
    }

    public function tags(mixed $tags): void
    {
        $this->query->scopes(['byTags' => [$tags]]);
    }
}
