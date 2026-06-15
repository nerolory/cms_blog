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
     * @param  CommentData  $data  данные формы

     * @return PostComment
     */
    public function create(CommentData $data): PostComment;

    /**
     * Возвращает visible root comments for post.
     *
     * @param  int  $postId  id
     */
    /**
     * Возвращает visible root comments for post.
     */
    /**
     * Возвращает корневые комментарии поста.
     *
     * @return Collection<int, PostComment>
     */
    public function getVisibleRootCommentsForPost(int $postId): Collection;

    /**
     * Находит by id.

     *
     * @return ?PostComment
     */
    public function findById(int $id): ?PostComment;

    /**
     * hide.

     *
     * @return PostComment
     */
    public function hide(PostComment $comment): PostComment;

    /**
     * Удаляет .

     *
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
     * @param  int  $postId  id

     * @return int
     */
    public function countVisibleForPost(int $postId): int;
}
