<?php

namespace App\Scopes;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ReportScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (hasRole(Roles::ADMIN)) {
            return;
        }

        /** @var User $user */
        $user = auth()->user();

        if ($user) {
            $builder->where('created_by', $user->id)
                ->orWhereHas('reportable', fn (Builder $q) => $q->where('created_by', $user->id));
        } else {
            $builder->where('id', null);
        }
    }
}
