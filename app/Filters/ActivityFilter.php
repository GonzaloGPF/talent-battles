<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class ActivityFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [
        'receiver' => [
            'receiver' => 'ids',
        ],
        'activityType' => [
            'activity_type' => 'ids',
        ],
    ];

    public function targetType($value): Builder
    {
        if (empty($value)) {
            return $this->query;
        }

        if (! is_array($value)) {
            $value = [$value];
        }

        $values = collect($value)
            ->filter(fn ($value) => is_string($value))
            ->map(fn ($value) => classByString($value))
            ->toArray();

        return $this->whereIn('target_type', $values);
    }
}
