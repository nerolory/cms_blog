<?php

namespace App\Services;

use App\DTO\CommentData;
use App\Exceptions\InvalidCommentException;
use App\Models\PostComment;
use App\Repositories\Contracts\CommentRepositoryContract;
use App\Services\Contracts\CommentServiceContract;
use App\Support\HtmlSanitizer;
use Illuminate\Support\Collection;

/**
 * Сервис comment.
 *
 * @property-read CommentRepositoryContract $comments
 */
class CommentService implements CommentServiceContract
{
    public function __construct(protected CommentRepositoryContract $comments) {}

    /**
     * Создаёт .
     *
     * @param  CommentData  $data  данные формы

     * @return PostComment
     */
    public function create(CommentData $data): PostComment
    {
        if ($data->body === '') {
            throw InvalidCommentException::emptyBody();
        }
        if ($data->parentId !== null) {
            $parent = $this->comments->findById($data->parentId);
            if ($parent === null) {
                throw InvalidCommentException::parentNotFound();
            }
            if ($parent->post_id !== $data->postId) {
                throw InvalidCommentException::parentMismatch();
            }
            if (! $parent->isRoot()) {
                throw InvalidCommentException::maxDepthExceeded();
            }
        }
        $sanitized = HtmlSanitizer::sanitizePlainText($data->body);

        return $this->comments->create(new CommentData(postId: $data->postId, userId: $data->userId, body: $sanitized,
            parentId: $data->parentId));
    }

    /**
     * Возвращает visible tree for post.
     *
     * @param  int  $postId  id
     */ /**
     * Возвращает дерево комментариев поста.
     *
     * @return Collection<int, PostComment>
     */
    public function getVisibleTreeForPost(int $postId): Collection
    {
        return $this->comments->getVisibleRootCommentsForPost($postId);
    }

    /**
     * hide.

     *
     * @return PostComment
     */
    public function hide(PostComment $comment): PostComment
    {
        return $this->comments->hide($comment);
    }

    /**
     * Удаляет .

     *
     * @return bool
     */
    public function delete(PostComment $comment): bool
    {
        return $this->comments->delete($comment);
    }

    /**
     * {@inheritdoc}

     *
     * @return PostComment
     */
    public function findForPost(int $postId, int $commentId): PostComment
    {
        $comment = $this->comments->findById($commentId);
        if ($comment === null || $comment->post_id !== $postId) {
            abort(404);
        }

        return $comment;
    }
}
