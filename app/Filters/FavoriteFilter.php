<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class FavoriteFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [
        'favoriteList' => [
            'favorite_list' => 'ids',
        ],
    ];

    public function type(string|array $values): Builder
    {
        if (! is_array($values)) {
            $values = [$values];
        }

        $values = collect($values)
            ->map(fn ($value) => classByString($value))
            ->toArray();

        $this->whereIn('favoritable_type', $values);

        return $this->query;
    }
}
