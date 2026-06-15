<?php

namespace App\Policies;

use App\Models\PostComment;
use App\Models\User;

/**
 * Политика доступа comment policy.
 */
class CommentPolicy
{
    /**
     * Создаёт .
     *
     * @param  ?User  $user  пользователь

     * @return bool
     */
    public function create(?User $user): bool
    {
        return $user !== null;
    }

    /**
     * reply.
     *
     * @param  ?User  $user  пользователь

     * @return bool
     */
    public function reply(?User $user, PostComment $comment): bool
    {
        return $user !== null && $comment->isRoot();
    }

    /**
     * hide.
     *
     * @param  ?User  $user  пользователь

     * @return bool
     */
    public function hide(?User $user, PostComment $comment): bool
    {
        return $user !== null && ($user->can('posts.moderate') || $user->hasRole(['admin', 'owner']));
    }

    /**
     * Удаляет .
     *
     * @param  ?User  $user  пользователь

     * @return bool
     */
    public function delete(?User $user, PostComment $comment): bool
    {
        if ($user === null) {
            return false;
        }
        if ($comment->user_id === $user->id) {
            return true;
        }

        return $user->can('posts.moderate') || $user->hasRole(['admin', 'owner']);
    }
}
