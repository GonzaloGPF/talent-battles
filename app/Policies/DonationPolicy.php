<?php

namespace App\Policies;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DonationPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Donation $donation): bool
    {
        if (! $donation->created_by) {
            return true;
        }

        return $user->id === $donation->created_by;
    }
}
