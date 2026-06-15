<?php

namespace App\Services\Contracts;

use App\DTO\AbstractData;
use App\DTO\PostWebMediaSyncInput;
use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Contract for post use-case orchestration.
 */
interface PostServiceContract
{
    /**
     * Returns the public post listing visible to the viewer.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function getPublicListing(?User $viewer): LengthAwarePaginator;

    /**
     * Creates a post from DTO data (delegates to admin pipeline with authenticated actor).

     *
     * @return Post
     */
    public function create(AbstractData $data): Post;

    /**
     * Creates a post from Filament/admin with moderation logging.

     *
     * @return Post
     */
    public function createFromAdmin(AbstractData $data, User $actor): Post;

    /**
     * Creates a post on behalf of an author (pending moderation).

     *
     * @return Post
     */
    public function createForAuthor(AbstractData $data, User $author): Post;

    /**
     * Updates post from DTO (delegates to admin pipeline with authenticated actor).

     *
     * @return bool
     */
    public function update(AbstractData $data, Post $post): bool;

    /**
     * Updates post from Filament/admin with moderation logging.

     *
     * @return Post
     */
    public function updateFromAdmin(AbstractData $data, Post $post, User $actor): Post;

    /**
     * Updates post from public web form (resolves moderation status).

     *
     * @return bool
     */
    public function updateFromWeb(AbstractData $data, Post $post, User $editor): bool;

    /**
     * Returns post for display or editing (without visibility check).

     *
     * @return Post
     */
    public function getPost(Post $post): Post;

    /**
     * Returns the post if visible to the viewer; otherwise 404.

     *
     * @return Post
     */
    public function getVisiblePost(Post $post, ?User $viewer): Post;

    /**
     * Загружает пост по slug и проверяет видимость для зрителя.

     *
     * @return Post
     */
    public function getVisiblePostBySlug(string $slug, ?User $viewer): Post;

    /**
     * Загружает пост по slug без проверки видимости (member CRUD).

     *
     * @return Post
     */
    public function getPostBySlug(string $slug): Post;

    /**
     * Загружает пост по id для API с проверкой видимости.

     *
     * @return Post
     */
    public function getPostForApi(int $postId, ?User $viewer): Post;

    /**
     * Загружает пост по id без проверки видимости (API mutations).

     *
     * @return Post
     */
    public function getPostById(int $postId): Post;

    /**
     * Checks whether the viewer may see the post.

     *
     * @return bool
     */
    public function canView(Post $post, ?User $viewer): bool;

    /**
     * Approves a post by a moderator or administrator.

     *
     * @return Post
     */
    public function approve(Post $post, User $moderator): Post;

    /**
     * Rejects a post with a reason.

     *
     * @return Post
     */
    public function reject(Post $post, User $moderator, string $reason): Post;

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
     * Syncs featured/background images from the public web form.

     *
     * @return Post
     */
    public function syncWebMedia(Post $post, PostWebMediaSyncInput $input): Post;
}
