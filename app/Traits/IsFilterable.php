<?php

namespace App\Traits;

use EloquentFilter\Filterable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

trait IsFilterable
{
    use Filterable;

    public int $defaultPerPage = 15;

    public int $maxLimit = 1000;

    public function scopeGetOrPaginate(Builder $query): Collection|LengthAwarePaginator
    {
        $perPage = request('per_page') ?? $this->defaultPerPage;
        $columns = request('columns') ?? ['*'];
        $limit = request('limit');

        if ($perPage > $this->maxLimit) {
            $perPage = $this->defaultPerPage;
        }

        if ($limit && $limit <= $this->maxLimit) {
            return $query->limit($limit)->get();
        } else {
            $paginator = $query->paginate($perPage, $columns, 'page', request('page'));

            $paginator->appends($this->filtered);

            return $paginator;
        }
    }
}
