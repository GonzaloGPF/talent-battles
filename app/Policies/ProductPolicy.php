<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ProductPolicy
{
    /**
     * Determine whether the user can update the model.
     *
     * @throws AuthorizationException
     */
    public function update(User $user, Product $product): bool
    {
        if ($product->created_by !== $user->id) {
            return false;
        }

        if ($product->isBlocked()) {
            return false;
        }

        if (! $user?->isAccountReady()) {
            throw new AuthorizationException(trans('exceptions.invalid_account'));
        }

        return true;
    }
}
