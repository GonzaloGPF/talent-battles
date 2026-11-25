<?php

namespace App\Filters;

use App\Models\Track;
use Illuminate\Database\Eloquent\Builder;

class ReportFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array<string, array<array-key, string>>
     */
    public $relations = [];

    public function message(mixed $value): void
    {
        $this->where('message', 'like', "%$value%");
    }

    public function resolution(mixed $value): void
    {
        $this->where('resolution', 'like', "%$value%");
    }

    public function solved(mixed $value): void
    {
        $this->nullable('resolution', $value);
    }

    public function hasCreation(mixed $value): void
    {
        $value
            ? $this->where('reportable_type', Track::class)
            : $this->where(fn (Builder $q) => $q->whereNull('reportable_type')->orWhere('reportable_type', '!=', Track::class));
    }
}
