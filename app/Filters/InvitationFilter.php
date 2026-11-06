<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class InvitationFilter extends Filter
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

    public function accepted($value): Builder
    {
        $this->nullable('user_id', $value);

        return $this->query;
    }

    public function hasAccount($value): Builder
    {
        if ($value) {
            $this->query->whereHas('user', fn (Builder $q) => $q->whereNotNull('account'));
        } else {
            $this->query->whereHas('user', fn (Builder $q) => $q->whereNull('account'));
        }

        return $this->query;
    }
}
