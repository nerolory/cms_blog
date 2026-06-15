<?php

namespace App\Services\Contracts;

use App\DTO\CommentData;
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
     * @param  CommentData  $data  данные формы

     * @return PostComment
     */
    public function create(CommentData $data): PostComment;

    /**
     * Возвращает visible tree for post.
     *
     * @param  int  $postId  id
     */
    /**
     * Возвращает visible tree for post.
     */
    /**
     * Возвращает дерево комментариев поста.
     *
     * @return Collection<int, PostComment>
     */
    public function getVisibleTreeForPost(int $postId): Collection;

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
     * Находит комментарий, принадлежащий посту.

     *
     * @return PostComment
     */
    public function findForPost(int $postId, int $commentId): PostComment;
}
