<?php

namespace App\Policies;

use App\Enums\Roles;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        if (hasRole(Roles::ADMIN, true, $user)) {
            return true;
        }

        return $user->id === $order->created_by;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Order $order): bool
    {
        if (! $user) {
            return true;
        }

        if (! $order->created_by) {
            return true;
        }

        return $user->id === $order->created_by;
    }
}
