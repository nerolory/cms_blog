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
    /**
     * Создаёт .
     *
     * @param  CommentData  $data
     * @return PostComment
     */
    public function create(CommentData $data): PostComment;

    /**
     * Возвращает visible root comments for post.
     *
     * @param  int  $postId
     * @param  int  $limit
     * @param  int  $offset
     * @return Collection<int, PostComment>
     */
    public function getVisibleRootCommentsForPost(int $postId, int $limit = 10, int $offset = 0): Collection;

    /**
     * count visible roots for post.
     *
     * @param  int  $postId
     * @return int
     */
    public function countVisibleRootsForPost(int $postId): int;

    /**
     * Возвращает visible thread replies.
     *
     * @param  int  $threadRootId
     * @param  int  $limit
     * @param  int  $offset
     * @return Collection<int, PostComment>
     */
    public function getVisibleThreadReplies(int $threadRootId, int $limit = 10, int $offset = 0): Collection;

    /**
     * count visible thread replies.
     *
     * @param  int  $threadRootId
     * @return int
     */
    public function countVisibleThreadReplies(int $threadRootId): int;

    /**
     * Находит by id.
     *
     * @param  int  $id
     * @return ?PostComment
     */
    public function findById(int $id): ?PostComment;

    /**
     * hide.
     *
     * @param  PostComment  $comment
     * @return PostComment
     */
    public function hide(PostComment $comment): PostComment;

    /**
     * Удаляет .
     *
     * @param  PostComment  $comment
     * @return bool
     */
    public function delete(PostComment $comment): bool;

    /**
     * count pending moderation.
     *
     * @return int
     */
    public function countPendingModeration(): int;

    /**
     * oldest pending age minutes.
     *
     * @return ?int
     */
    public function oldestPendingAgeMinutes(): ?int;

    /**
     * count visible for post.
     *
     * @param  int  $postId
     * @return int
     */
    public function countVisibleForPost(int $postId): int;

    /**
     * Возвращает visible section stats.
     *
     * @param  int  $postId
     * @return Collection<string, int>
     */
    public function getVisibleSectionStats(int $postId): Collection;

    /**
     * count visible replies by root ids.
     *
     * @param  Collection<int, int>  $rootIds
     * @return Collection<int, int>
     */
    public function countVisibleRepliesByRootIds(Collection $rootIds): Collection;
}
