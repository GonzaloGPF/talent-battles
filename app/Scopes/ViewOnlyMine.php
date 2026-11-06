<?php

namespace App\Scopes;

use App\Enums\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ViewOnlyMine implements Scope
{
    public function __construct(
        private readonly bool $allowAdmin = false,
        private readonly string $column = 'created_by'
    ) {}

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if ($this->allowAdmin && hasRole(Roles::ADMIN)) {
            return;
        }

        $table = $builder->getModel()->getTable();

        $builder->where("$table.$this->column", auth()->id() ?? 0);
    }
}
