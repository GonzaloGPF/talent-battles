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

    /**
     * The first `limit` records, or a page of `per_page` records. A limit or a page size
     * outside 1 to maxLimit is ignored, and so is a page that is not a positive number.
     *
     * @param  Builder<self>  $query
     * @return Collection<int, self>|LengthAwarePaginator<int, self>
     */
    public function scopeGetOrPaginate(Builder $query): Collection|LengthAwarePaginator
    {
        $limit = request()->integer('limit');

        if ($this->isWithinMaxLimit($limit)) {
            return $query->limit($limit)->get();
        }

        $perPage = request()->integer('per_page');
        $page = max(1, request()->integer('page', 1));

        $paginator = $query->paginate($this->isWithinMaxLimit($perPage) ? $perPage : $this->defaultPerPage, ['*'], 'page', $page);

        $paginator->appends($this->filtered);

        return $paginator;
    }

    private function isWithinMaxLimit(int $value): bool
    {
        return $value >= 1 && $value <= $this->maxLimit;
    }
}
