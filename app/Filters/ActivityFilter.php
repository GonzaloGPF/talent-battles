<?php

namespace App\Filters;

class ActivityFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array<string, array<array-key, string>>
     */
    public $relations = [
        'receiver' => [
            'receiver' => 'ids',
        ],
        'activityType' => [
            'activity_type' => 'ids',
        ],
    ];

    public function targetType(mixed $value): void
    {
        if (empty($value)) {
            return;
        }

        if (! is_array($value)) {
            $value = [$value];
        }

        $values = collect($value)
            ->filter(fn ($value) => is_string($value))
            ->map(fn ($value) => classByString($value))
            ->toArray();

        $this->whereIn('target_type', $values);
    }
}
