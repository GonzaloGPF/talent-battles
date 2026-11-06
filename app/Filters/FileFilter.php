<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class FileFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    protected function mime($mime): Builder
    {
        $this->where('mime', 'like', "%$mime%");

        return $this->query;
    }

    protected function model($modelId): Builder
    {
        $this->where('fileable_id', $modelId);

        return $this->query;
    }

    protected function modelType($modelType): Builder
    {
        $this->where('fileable_type', classByString($modelType));

        return $this->query;
    }
}
