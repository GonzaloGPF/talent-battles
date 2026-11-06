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
     * @var array
     */
    public $relations = [];

    public function message($value): Builder
    {
        $this->where('message', 'like', "%$value%");

        return $this->query;
    }

    public function resolution($value): Builder
    {
        $this->where('resolution', 'like', "%$value%");

        return $this->query;
    }

    protected function solved($value): Builder
    {
        return $this->nullable('resolution', $value);
    }

    protected function hasCreation($value): Builder
    {
        $value
            ? $this->where('reportable_type', Track::class)
            : $this->where(fn (Builder $q) => $q->whereNull('reportable_type')->orWhere('reportable_type', '!=', Track::class));

        return $this->query;
    }
}
