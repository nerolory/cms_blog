<?php

namespace App\Services\Contracts;

use App\DTO\CommentData;
use App\DTO\CommentSectionData;
use App\Models\PostComment;
use Illuminate\Support\Collection;

/**
 * Контракт сервиса comment.
 */
interface CommentServiceContract
{
    public function create(CommentData $data): PostComment;

    public function getSectionForPost(int $postId, ?int $userId): CommentSectionData;

    public function forgetSectionCacheForPost(int $postId): void;

    /**
     * @return Collection<int, PostComment>
     */
    public function getRootPage(int $postId, int $offset): Collection;

    /**
     * @return array{replies: Collection<int, PostComment>, hasMore: bool, total: int}
     */
    public function getThreadRepliesPage(int $threadRootId, int $offset): array;

    /**
     * @return Collection<int, PostComment>
     */
    public function getVisibleTreeForPost(int $postId): Collection;

    public function countRootsForPost(int $postId): int;

    /**
     * @param  list<int>  $rootIds
     * @return Collection<int, int>
     */
    public function replyCountsForRoots(array $rootIds): Collection;

    public function hide(PostComment $comment): PostComment;

    public function delete(PostComment $comment): bool;

    public function findForPost(int $postId, int $commentId): PostComment;
}
