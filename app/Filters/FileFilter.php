<?php

namespace App\Filters;

class FileFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array<string, array<array-key, string>>
     */
    public $relations = [];

    public function mime(mixed $mime): void
    {
        $this->where('mime', 'like', "%$mime%");
    }

    public function model(mixed $modelId): void
    {
        $this->where('fileable_id', $modelId);
    }

    public function modelType(mixed $modelType): void
    {
        $this->where('fileable_type', classByString($modelType));
    }
}
