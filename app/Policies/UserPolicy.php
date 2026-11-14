<?php

namespace App\Policies;

use App\Enums\Roles;
use App\Models\User;
use App\Services\ProfileClaimService;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function __construct(private readonly ProfileClaimService $profileClaimService) {}

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        return hasRole(Roles::ADMIN) || hasRole(Roles::MANAGER);
    }

    /**
     * Determine whether the user can update the provider.
     */
    public function update(User $user, User $userToUpdate): bool
    {
        return hasRole(Roles::ADMIN) || hasRole(Roles::MANAGER) || $user->id === $userToUpdate->id;
    }

    /**
     * Determine whether the user can delete the provider.
     */
    public function delete(User $user, User $userToDelete): bool
    {
        return hasRole(Roles::ADMIN) || $user->is($userToDelete);
    }

    /**
     * Admins hand profiles to their owners; staff accounts are never handed over.
     */
    public function createClaimLink(User $user, User $profile): bool
    {
        return hasRole(Roles::ADMIN) && $profile->role === Roles::USER;
    }

    /**
     * Whoever holds a link that still matches the profile decides what happens to it, signed in or not.
     */
    public function claim(?User $user, User $profile, mixed $key): bool
    {
        return $this->profileClaimService->isValidKey($profile, $key);
    }
}
