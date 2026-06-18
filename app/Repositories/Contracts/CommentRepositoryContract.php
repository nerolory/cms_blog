<?php

namespace App\Repositories\Contracts;

use App\DTO\CommentData;
use App\Models\PostComment;
use Illuminate\Support\Collection;

/**
 * Контракт репозитория comment.
 */
interface CommentRepositoryContract
{
    public function create(CommentData $data): PostComment;

    /**
     * @return Collection<int, PostComment>
     */
    public function getVisibleRootCommentsForPost(int $postId, int $limit = 10, int $offset = 0): Collection;

    public function countVisibleRootsForPost(int $postId): int;

    /**
     * @return Collection<int, PostComment>
     */
    public function getVisibleThreadReplies(int $threadRootId, int $limit = 10, int $offset = 0): Collection;

    public function countVisibleThreadReplies(int $threadRootId): int;

    public function findById(int $id): ?PostComment;

    public function hide(PostComment $comment): PostComment;

    public function delete(PostComment $comment): bool;

    public function countPendingModeration(): int;

    public function oldestPendingAgeMinutes(): ?int;

    public function countVisibleForPost(int $postId): int;

    /**
     * @return array{totalRoots: int, totalVisible: int}
     */
    public function getVisibleSectionStats(int $postId): array;

    /**
     * @param  list<int>  $rootIds
     * @return Collection<int, int>
     */
    public function countVisibleRepliesByRootIds(array $rootIds): Collection;
}
