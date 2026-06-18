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
            'parent_id' => $data->threadRootId, 'reply_to_id' => $data->replyToId, 'body' => $data->body,
            'status' => CommentStatus::Visible]);
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibleRootCommentsForPost(int $postId, int $limit = 10, int $offset = 0): Collection
    {
        return $this->comment->newQuery()->with('user')->where('post_id', $postId)->whereNull('parent_id')
            ->where('status', CommentStatus::Visible)->orderByDesc('created_at')->offset($offset)->limit($limit)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function countVisibleRootsForPost(int $postId): int
    {
        return $this->comment->newQuery()->where('post_id', $postId)->whereNull('parent_id')
            ->where('status', CommentStatus::Visible)->count();
    }

    /**
     * Возвращает visible thread replies.
     */
    public function getVisibleThreadReplies(int $threadRootId, int $limit = 10, int $offset = 0): Collection
    {
        return $this->comment->newQuery()->with(['user', 'replyTo.user'])->where('parent_id', $threadRootId)
            ->where('status', CommentStatus::Visible)->orderBy('created_at')->offset($offset)->limit($limit)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function countVisibleThreadReplies(int $threadRootId): int
    {
        return $this->comment->newQuery()->where('parent_id', $threadRootId)
            ->where('status', CommentStatus::Visible)->count();
    }

    /**
     * Находит by id.

     *
     * @return ?PostComment
     */
    public function findById(int $id): ?PostComment
    {
        return $this->comment->newQuery()->with(['post', 'parent', 'replyTo.user'])->find($id);
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

    /**
     * {@inheritdoc}
     *
     * @return array{totalRoots: int, totalVisible: int}
     */
    public function getVisibleSectionStats(int $postId): array
    {
        /** @var object{total_roots: int|string, total_visible: int|string}|null $row */
        $row = $this->comment->newQuery()
            ->where('post_id', $postId)
            ->where('status', CommentStatus::Visible)
            ->selectRaw('COUNT(*) as total_visible')
            ->selectRaw('COUNT(*) FILTER (WHERE parent_id IS NULL) as total_roots')
            ->first();
        if ($row === null) {
            return ['totalRoots' => 0, 'totalVisible' => 0];
        }

        return [
            'totalRoots' => (int) $row->total_roots,
            'totalVisible' => (int) $row->total_visible,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param  list<int>  $rootIds
     * @return Collection<int, int>
     */
    public function countVisibleRepliesByRootIds(array $rootIds): Collection
    {
        if ($rootIds === []) {
            return collect();
        }

        return $this->comment->newQuery()
            ->selectRaw('parent_id, COUNT(*) as aggregate')
            ->whereIn('parent_id', $rootIds)
            ->where('status', CommentStatus::Visible)
            ->groupBy('parent_id')
            ->pluck('aggregate', 'parent_id')
            ->mapWithKeys(fn (mixed $count, mixed $parentId): array => [(int) $parentId => (int) $count]);
    }
}
