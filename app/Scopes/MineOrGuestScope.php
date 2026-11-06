<?php

namespace App\Scopes;

use App\Enums\Roles;
use App\Services\CartService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class MineOrGuestScope implements Scope
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

        if (auth()->check()) {
            $builder->where('created_by', auth()->id());
        } else {
            $builder->where('guest_id', CartService::getGuestId())
                ->whereNotNull('guest_id');
        }
    }
}
