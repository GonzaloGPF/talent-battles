<?php

namespace App\Filters;

class FavoriteFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array<string, array<array-key, string>>
     */
    public $relations = [
        'favoriteList' => [
            'favorite_list' => 'ids',
        ],
    ];

    /**
     * @param  string|list<string>  $values
     */
    public function type(string|array $values): void
    {
        if (! is_array($values)) {
            $values = [$values];
        }

        $values = collect($values)
            ->map(fn ($value) => classByString($value))
            ->toArray();

        $this->whereIn('favoritable_type', $values);
    }
}
