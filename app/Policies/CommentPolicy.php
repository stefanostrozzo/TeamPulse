<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the comment.
     * Only users who have the manage comments permission is allowed to edit the content.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->hasPermissionTo('manage comments');
    }

    /**
     * Determine whether the user can delete the comment.
     * Permission is granted to the author or users with higher administrative roles.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->hasPermissionTo('delete comments');
    }
}
