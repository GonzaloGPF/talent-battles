<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class UserFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function email($value): Builder
    {
        $this->where('email', 'like', "%$value%");

        return $this->query;
    }

    public function verified($value): Builder
    {
        $this->nullable('verified_at', $value);

        return $this->query;
    }

    public function skills($values): Builder
    {
        collect($values)
            ->each(fn (string $value) => $this->where('skills', 'like', "%$value%"));

        return $this->query;
    }

    public function categories($values): Builder
    {
        collect($values)
            ->each(fn (string $value) => $this->where('categories', 'like', "%$value%"));

        return $this->query;
    }

    public function nick($value): Builder
    {
        $this->where('nick', 'like', "%$value%");

        return $this->query;
    }

    public function nickName($value): Builder
    {
        $this->where(function (Builder $builder) use ($value) {
            $builder->where('name', 'like', "%$value%")
                ->orWhere('nick', 'like', "%$value%");
        });

        return $this->query;
    }
}
