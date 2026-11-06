<?php

namespace App\Policies;

use App\Enums\Roles;
use App\Models\File;
use App\Models\Product;
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

    public function view(?User $user, File $file): bool
    {
        $fileable = $file->fileable;

        if ($fileable instanceof Product) {
            $file = $fileable->file;

            if (! $user) {
                return request('secret') === $file->secret;
            }

            return $user->id === $file->created_by;
        }

        return true;
    }

    public function update(User $user, File $file): bool
    {
        return hasRole(Roles::ADMIN) || in_array($file->id, $user->files->modelKeys());
    }
}
