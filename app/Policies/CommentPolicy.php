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
     * Only the author of the comment is allowed to edit the content.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    /**
     * Determine whether the user can delete the comment.
     * Permission is granted to the author or users with higher administrative roles.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // Users can delete their own comments
        if ($user->id === $comment->user_id) {
            return true;
        }

        // Optional: Allow project managers/admins to delete any comment
        return $user->hasPermissionTo('delete tasks');
    }
}
