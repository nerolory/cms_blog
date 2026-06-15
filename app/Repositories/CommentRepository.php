<?php

namespace App\Repositories;

use App\DTO\CommentData;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Models\PostComment;
use App\Repositories\Contracts\CommentRepositoryContract;
use Illuminate\Support\Collection;

/**
 * Репозиторий comment.
 *
 * @property-read PostComment $comment
 */
class CommentRepository implements CommentRepositoryContract
{
    public function __construct(protected PostComment $comment) {}

    /**
     * Создаёт .
     *
     * @param  CommentData  $data  данные формы

     * @return PostComment
     */
    public function create(CommentData $data): PostComment
    {
        return $this->comment->newQuery()->create(['post_id' => $data->postId, 'user_id' => $data->userId,
            'parent_id' => $data->parentId, 'body' => $data->body, 'status' => CommentStatus::Visible]);
    }

    /**
     * Возвращает visible root comments for post.
     *
     * @param  int  $postId  id
     */ /**
     * Возвращает корневые комментарии поста.
     *
     * @return Collection<int, PostComment>
     */
    public function getVisibleRootCommentsForPost(int $postId): Collection
    {
        return $this->comment->newQuery()->with(['user', 'replies' => fn ($query) => $query->where('status',
            CommentStatus::Visible)->with('user')->orderBy('created_at')])->where('post_id',
                $postId)->whereNull('parent_id')->where('status', CommentStatus::Visible)->orderBy('created_at')->get();
    }

    /**
     * Находит by id.

     *
     * @return ?PostComment
     */
    public function findById(int $id): ?PostComment
    {
        return $this->comment->newQuery()->with(['post', 'parent'])->find($id);
    }

    /**
     * hide.

     *
     * @return PostComment
     */
    public function hide(PostComment $comment): PostComment
    {
        $comment->update(['status' => CommentStatus::Hidden]);

        return $comment->fresh() ?? $comment;
    }

    /**
     * Удаляет .

     *
     * @return bool
     */
    public function delete(PostComment $comment): bool
    {
        return (bool) $comment->delete();
    }

    /**
     * count pending moderation.

     *
     * @return int
     */
    public function countPendingModeration(): int
    {
        return $this->comment->newQuery()->where('status', CommentStatus::Visible)->whereHas('post',
            fn ($query) => $query->where('status', PostStatus::PendingModeration))->count();
    }

    /**
     * oldest pending age minutes.

     *
     * @return ?int
     */
    public function oldestPendingAgeMinutes(): ?int
    {
        $oldest = $this->comment->newQuery()->where('status', CommentStatus::Visible)->whereHas('post',
            fn ($query) => $query->where('status', PostStatus::PendingModeration))->min('created_at');
        if (! is_string($oldest) && ! $oldest instanceof \DateTimeInterface) {
            return null;
        }

        return (int) now()->diffInMinutes($oldest);
    }

    /**
     * count visible for post.
     *
     * @param  int  $postId  id

     * @return int
     */
    public function countVisibleForPost(int $postId): int
    {
        return $this->comment->newQuery()->where('post_id', $postId)->where('status', CommentStatus::Visible)->count();
    }
}
