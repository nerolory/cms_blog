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
    /**
     * Создаёт .
     *
     * @param  CommentData  $data
     * @return PostComment
     */
    public function create(CommentData $data): PostComment;

    /**
     * Возвращает section for post.
     *
     * @param  int  $postId
     * @param  ?int  $userId
     * @return CommentSectionData
     */
    public function getSectionForPost(int $postId, ?int $userId): CommentSectionData;

    /**
     * forget section cache for post.
     *
     * @param  int  $postId
     */
    public function forgetSectionCacheForPost(int $postId): void;

    /**
     * Возвращает root page.
     *
     * @param  int  $postId
     * @param  int  $offset
     * @return Collection<int, PostComment>
     */
    public function getRootPage(int $postId, int $offset): Collection;

    /**
     * Возвращает thread replies page.
     *
     * @param  int  $threadRootId
     * @param  int  $offset
     * @return Collection<string, mixed>
     */
    public function getThreadRepliesPage(int $threadRootId, int $offset): Collection;

    /**
     * Возвращает visible tree for post.
     *
     * @param  int  $postId
     * @return Collection<int, PostComment>
     */
    public function getVisibleTreeForPost(int $postId): Collection;

    /**
     * count roots for post.
     *
     * @param  int  $postId
     * @return int
     */
    public function countRootsForPost(int $postId): int;

    /**
     * reply counts for roots.
     *
     * @param  Collection<int, int>  $rootIds
     * @return Collection<int, int>
     */
    public function replyCountsForRoots(Collection $rootIds): Collection;

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
     * Находит for post.
     *
     * @param  int  $postId
     * @param  int  $commentId
     * @return PostComment
     */
    public function findForPost(int $postId, int $commentId): PostComment;
}
