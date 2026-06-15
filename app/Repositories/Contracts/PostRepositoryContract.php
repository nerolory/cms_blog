<?php

namespace App\Repositories\Contracts;

use App\DTO\AbstractData;
use App\DTO\PostMediaData;
use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Contract for post data access.
 */
interface PostRepositoryContract
{
    /**
     * Returns the public post listing visible to the viewer.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function getPublicListing(?User $viewer): LengthAwarePaginator;

    /**
     * Creates a post record in the database.

     *
     * @return Post
     */
    public function create(AbstractData $data): Post;

    /**
     * Loads a post from route model binding.

     *
     * @return Post
     */
    public function getPost(Post $post): Post;

    /**
     * Загружает пост по slug с relations (один запрос).

     *
     * @return Post
     */
    public function findBySlug(string $slug): Post;

    /**
     * Загружает пост по id с relations (один запрос).

     *
     * @return Post
     */
    public function findById(int $id): Post;

    /**
     * Находит by id or null.

     *
     * @return ?Post
     */
    public function findByIdOrNull(int $id): ?Post;

    /**
     * load author.

     *
     * @return Post
     */
    public function loadAuthor(Post $post): Post;

    /**
     * Обновляет category id.
     */
    public function updateCategoryId(Post $post, int $categoryId): void;

    /**
     * Возвращает посты для отложенной публикации.
     *
     * @return Collection<int, Post>
     */
    public function getDueForScheduledPublish(): Collection;

    /**
     * clear scheduled publish at.

     *
     * @return Post
     */
    public function clearScheduledPublishAt(Post $post): Post;

    /**
     * count pending moderation.

     *
     * @return int
     */
    public function countPendingModeration(): int;

    /**
     * oldest pending moderation updated at.

     *
     * @return mixed
     */
    public function oldestPendingModerationUpdatedAt(): mixed;

    /**
     * Перезагружает пост с relations user и requiredPermission.

     *
     * @return Post
     */
    public function refreshWithRelations(Post $post): Post;

    /**
     * Updates a post record in the database.

     *
     * @return bool
     */
    public function update(AbstractData $data, Post $post): bool;

    /**
     * Updates featured and background image paths.

     *
     * @return Post
     */
    public function updateMediaPaths(Post $post, PostMediaData $media): Post;

    /**
     * Soft-deletes the post.

     *
     * @return ?bool
     */
    public function destroy(Post $post): ?bool;

    /**
     * Restores a soft-deleted post.

     *
     * @return Post
     */
    public function restore(Post $post): Post;

    /**
     * Checks whether the viewer may see the post.

     *
     * @return bool
     */
    public function isVisibleToViewer(Post $post, ?User $viewer): bool;

    /**
     * Approves a post (publishes it).

     *
     * @return Post
     */
    public function approve(Post $post): Post;

    /**
     * Rejects a post with a reason.

     *
     * @return Post
     */
    public function reject(Post $post, string $reason): Post;
}
