<?php

namespace App\Policies;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use App\Services\Contracts\PostServiceContract;
use App\Support\Post\VisibilityChecker;

/**
 * Authorization policy for posts.
 * Domain rules apply on the public site; Shield permissions apply in Filament /admin.
 *
 * @property-read PostServiceContract $postService
 * @property-read VisibilityChecker $visibilityChecker
 */
class PostPolicy
{
    public function __construct(protected PostServiceContract $postService,
        protected VisibilityChecker $visibilityChecker) {}

    /**
     * Post listing in Filament.

     *
     * @return bool
     */
    public function viewAny(?User $user): bool
    {
        return $user !== null && $user->can('ViewAny:Post');
    }

    /**
     * View a single post.

     *
     * @return bool
     */
    public function view(?User $user, Post $post): bool
    {
        if ($user !== null && $user->can('View:Post')) {
            return true;
        }

        return $this->postService->canView($post, $user);
    }

    /**
     * Create a post.

     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->canCreatePosts() || $user->can('Create:Post');
    }

    /**
     * Update a post.

     *
     * @return bool
     */
    public function update(User $user, Post $post): bool
    {
        if ($user->can('Update:Post')) {
            return true;
        }
        if ($user->can('posts.manage.all') || $user->hasRole(['admin', 'owner'])) {
            return true;
        }
        if ($user->can('posts.moderate') && $post->status === PostStatus::PendingModeration) {
            return true;
        }
        if (! $post->isOwnedBy($user)) {
            return false;
        }

        return $user->can('posts.update.own') && in_array($post->status, [PostStatus::Draft,
            PostStatus::PendingModeration, PostStatus::Rejected, PostStatus::Published], true);
    }

    /**
     * Delete a post.

     *
     * @return bool
     */
    public function delete(User $user, Post $post): bool
    {
        if ($user->can('Delete:Post')) {
            return true;
        }
        if ($user->can('posts.manage.all') || $user->hasRole(['admin', 'owner'])) {
            return true;
        }
        if (! $post->isOwnedBy($user)) {
            return false;
        }

        return $user->can('posts.delete.own');
    }

    /**
     * Preview a post draft before publishing.

     *
     * @return bool
     */
    public function preview(User $user, ?Post $post = null): bool
    {
        if ($post === null) {
            return $this->create($user);
        }

        return $this->update($user, $post);
    }

    /**
     * Moderate a post.

     *
     * @return bool
     */
    public function moderate(User $user, Post $post): bool
    {
        if (! $user->can('posts.moderate') || $post->status !== PostStatus::PendingModeration) {
            return false;
        }
        $post->loadMissing('user');

        return $this->visibilityChecker->isModeratableBy($post, $user);
    }

    /**
     * Удаляет any.
     *
     * @param  User  $user  пользователь

     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('DeleteAny:Post');
    }

    /**
     * restore.
     *
     * @param  User  $user  пользователь
     * @param  Post  $post  пост

     * @return bool
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->can('Restore:Post') || $user->hasRole(['admin', 'owner']);
    }

    /**
     * force delete.
     *
     * @param  User  $user  пользователь
     * @param  Post  $post  пост

     * @return bool
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $user->can('ForceDelete:Post') || $user->hasRole('owner');
    }

    /**
     * force delete any.
     *
     * @param  User  $user  пользователь

     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('ForceDeleteAny:Post');
    }

    /**
     * restore any.
     *
     * @param  User  $user  пользователь

     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('RestoreAny:Post');
    }

    /**
     * replicate.
     *
     * @param  User  $user  пользователь
     * @param  Post  $post  пост

     * @return bool
     */
    public function replicate(User $user, Post $post): bool
    {
        return $user->can('Replicate:Post');
    }

    /**
     * reorder.
     *
     * @param  User  $user  пользователь

     * @return bool
     */
    public function reorder(User $user): bool
    {
        return $user->can('Reorder:Post');
    }
}
