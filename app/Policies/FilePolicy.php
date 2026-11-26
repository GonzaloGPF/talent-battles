<?php

namespace App\Policies;

use App\Enums\Roles;
use App\Models\File;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Anyone may download a public file. A private one is for its owner, the admins and whoever has its secret link.
     */
    public function view(?User $user, File $file): bool
    {
        if (! $file->isPrivate()) {
            return true;
        }

        if ($user && ($user->id === $file->created_by || hasRole(Roles::ADMIN, user: $user))) {
            return true;
        }

        $secret = request('secret');

        return is_string($secret) && hash_equals($file->secret, $secret);
    }

    public function update(User $user, File $file): bool
    {
        return hasRole(Roles::ADMIN) || in_array($file->id, $user->files->modelKeys());
    }
}
