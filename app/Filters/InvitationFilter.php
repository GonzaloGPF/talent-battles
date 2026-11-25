<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class InvitationFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array<string, array<array-key, string>>
     */
    public $relations = [];

    public function email(mixed $value): void
    {
        $this->where('email', 'like', "%$value%");
    }

    public function accepted(mixed $value): void
    {
        $this->nullable('user_id', $value);
    }

    public function hasAccount(mixed $value): void
    {
        if ($value) {
            $this->query->whereHas('user', fn (Builder $q) => $q->whereNotNull('account'));
        } else {
            $this->query->whereHas('user', fn (Builder $q) => $q->whereNull('account'));
        }
    }
}
