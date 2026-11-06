<?php

namespace App\Policies;

use App\Enums\Roles;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Comment $comment): bool
    {
        return hasRole(Roles::ADMIN) || $comment->created_by === $user->id;
    }
}
