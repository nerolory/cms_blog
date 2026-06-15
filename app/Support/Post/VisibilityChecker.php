<?php

namespace App\Support\Post;

use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

/**
 * Visibility rules for public post listing and single-post access.
 */
final class VisibilityChecker
{
    /**
     * apply public listing scope.
     *
     * @param  Builder<Post>  $query
     */
    public function applyPublicListingScope(Builder $query, ?User $viewer): void
    {
        $query->where(function (Builder $inner) use ($viewer): void {
            $visibilityValues = [PostVisibility::Guest->value];
            if ($viewer !== null) {
                $visibilityValues[] = PostVisibility::Authenticated->value;
            }
            if ($viewer !== null && $viewer->hasRole(['admin', 'owner'])) {
                $visibilityValues[] = PostVisibility::Admin->value;
            }
            $inner->whereIn('visibility', $visibilityValues);
            if ($viewer === null) {
                return;
            }
            $permissionIds = $viewer->getAllPermissions()
                ->filter(fn (mixed $permission): bool => $permission instanceof Permission)->pluck('id')
                ->filter(fn (mixed $id): bool => is_int($id) || is_string($id));
            if ($permissionIds->isNotEmpty()) {
                $inner->orWhere(function (Builder $permissionQuery) use ($permissionIds): void {
                    $permissionQuery->where('visibility',
                        PostVisibility::Permission->value)->whereIn('required_permission_id', $permissionIds);
                });
            }
        });
    }

    /**
     * matches visibility.
     *
     * @param  Post  $post  пост

     * @return bool
     */
    public function matchesVisibility(Post $post, ?User $viewer): bool
    {
        $visibility = PostVisibility::tryFrom($post->visibility);

        return match ($visibility) {
            PostVisibility::Guest => true,
            PostVisibility::Authenticated => $viewer !== null,
            PostVisibility::Admin => $viewer !== null && $viewer->hasRole(['admin', 'owner']),
            PostVisibility::Permission => $this->matchesPermissionVisibility($post, $viewer),
            default => false,
        };
    }

    /**
     * apply moderation queue scope.
     *
     * @param  Builder<Post>  $query
     */
    public function applyModerationQueueScope(Builder $query): void
    {
        $query->where('visibility', '!=', PostVisibility::Admin->value)->where('visibility', '!=',
            PostVisibility::Permission->value)->whereDoesntHave('user.roles', function (Builder $roleQuery): void {
                $roleQuery->whereIn('name', ['admin', 'owner']);
            });
    }

    /**
     * Проверяет moderatable by.
     *
     * @param  Post  $post  пост

     * @return bool
     */
    public function isModeratableBy(Post $post, User $viewer): bool
    {
        if ($post->status !== PostStatus::PendingModeration) {
            return false;
        }
        if ($post->visibility === PostVisibility::Admin->value) {
            return false;
        }
        if ($post->visibility === PostVisibility::Permission->value) {
            return false;
        }
        if ($post->user !== null && $post->user->hasRole(['admin', 'owner'])) {
            return false;
        }

        return true;
    }

    private function matchesPermissionVisibility(Post $post, ?User $viewer): bool
    {
        if ($viewer === null || $post->required_permission_id === null) {
            return false;
        }

        return $viewer->getAllPermissions()->contains('id', $post->required_permission_id);
    }
}
