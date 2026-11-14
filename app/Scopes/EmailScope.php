<?php

namespace App\Scopes;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class EmailScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (hasRole(Roles::ADMIN)) {
            return;
        }

        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where(function (Builder $builder) use ($user) {
            $builder->whereJsonContains('to', $user->email)
                ->orWhere('created_by', $user->id);
        });
    }
}
