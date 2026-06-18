<?php

namespace App\Services;

use App\DTO\CommentData;
use App\DTO\CommentReactionSummary;
use App\DTO\CommentSectionData;
use App\Enums\ReactionType;
use App\Exceptions\InvalidCommentException;
use App\Models\PostComment;
use App\Models\User;
use App\Repositories\Contracts\CommentRepositoryContract;
use App\Services\Contracts\CommentReactionServiceContract;
use App\Services\Contracts\CommentServiceContract;
use App\Services\Contracts\PostEngagementVersionServiceContract;
use App\Support\Cache\ApplicationCacheKeys;
use App\Support\HtmlSanitizer;
use App\Support\TypeCast;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

/**
 * Сервис comment.
 */
class CommentService implements CommentServiceContract
{
    public const ROOT_PAGE_SIZE = 20;

    public const REPLIES_PAGE_SIZE = 20;

    private const SECTION_CACHE_TTL_SECONDS = 3600;

    public function __construct(
        protected CommentRepositoryContract $comments,
        protected CommentReactionServiceContract $commentReactions,
        protected PostEngagementVersionServiceContract $engagementVersions,
    ) {}

    public function create(CommentData $data): PostComment
    {
        if ($data->body === '') {
            throw InvalidCommentException::emptyBody();
        }

        $placement = $this->resolveReplyPlacement($data);
        $sanitized = HtmlSanitizer::sanitizePlainText($data->body);

        $comment = $this->comments->create(new CommentData(
            postId: $data->postId,
            userId: $data->userId,
            body: $sanitized,
            threadRootId: $placement['threadRootId'],
            replyToId: $placement['replyToId'],
        ));
        $this->forgetSectionCache($data->postId);

        return $comment;
    }

    public function getSectionForPost(int $postId, ?int $userId): CommentSectionData
    {
        $payload = $this->resolveSectionPayload($postId);
        $roots = $this->hydrateRootComments(TypeCast::array($payload['roots'] ?? []));
        $totalRoots = TypeCast::int($payload['total_roots'] ?? 0);
        $totalVisible = TypeCast::int($payload['total_visible'] ?? 0);
        /** @var array<int|string, int|string> $replyCountsRaw */
        $replyCountsRaw = TypeCast::array($payload['reply_counts'] ?? []);
        $replyCounts = collect();
        foreach ($replyCountsRaw as $rootId => $count) {
            $replyCounts->put((int) $rootId, TypeCast::int($count));
        }
        $rootIds = $roots->pluck('id')->map(fn (mixed $id): int => TypeCast::int($id))->all();
        /** @var array<int, array<string, int>> $reactionAggregates */
        $reactionAggregates = TypeCast::array($payload['reaction_aggregates'] ?? []);

        return new CommentSectionData(
            rootComments: $roots,
            hasMoreRoots: $totalRoots > $roots->count(),
            totalRoots: $totalRoots,
            totalVisibleComments: $totalVisible,
            replyCounts: $replyCounts,
            reactionSummaries: $this->buildReactionSummaries($rootIds, $reactionAggregates, $userId),
        );
    }

    public function forgetSectionCacheForPost(int $postId): void
    {
        $this->forgetSectionCache($postId);
    }

    /**
     * @return Collection<int, PostComment>
     */
    public function getRootPage(int $postId, int $offset): Collection
    {
        return $this->comments->getVisibleRootCommentsForPost($postId, self::ROOT_PAGE_SIZE, $offset);
    }

    /**
     * @return array{replies: Collection<int, PostComment>, hasMore: bool, total: int}
     */
    public function getThreadRepliesPage(int $threadRootId, int $offset): array
    {
        $replies = $this->comments->getVisibleThreadReplies($threadRootId, self::REPLIES_PAGE_SIZE, $offset);
        $total = $this->comments->countVisibleThreadReplies($threadRootId);

        return [
            'replies' => $replies,
            'hasMore' => $total > $offset + $replies->count(),
            'total' => $total,
        ];
    }

    public function getVisibleTreeForPost(int $postId): Collection
    {
        return $this->comments->getVisibleRootCommentsForPost($postId, self::ROOT_PAGE_SIZE);
    }

    public function countRootsForPost(int $postId): int
    {
        return $this->comments->countVisibleRootsForPost($postId);
    }

    /**
     * {@inheritdoc}
     */
    public function replyCountsForRoots(array $rootIds): Collection
    {
        return $this->comments->countVisibleRepliesByRootIds($rootIds);
    }

    public function hide(PostComment $comment): PostComment
    {
        $hidden = $this->comments->hide($comment);
        $this->forgetSectionCache($comment->post_id);

        return $hidden;
    }

    public function delete(PostComment $comment): bool
    {
        $deleted = $this->comments->delete($comment);
        if ($deleted) {
            $this->forgetSectionCache($comment->post_id);
        }

        return $deleted;
    }

    public function findForPost(int $postId, int $commentId): PostComment
    {
        $comment = $this->comments->findById($commentId);
        if ($comment === null || $comment->post_id !== $postId) {
            abort(404);
        }

        return $comment;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveSectionPayload(int $postId): array
    {
        $cacheKey = ApplicationCacheKeys::postCommentSection($postId);
        try {
            $cached = Redis::get($cacheKey);
            if (is_string($cached) && $cached !== '') {
                /** @var array<string, mixed> $decoded */
                $decoded = json_decode($cached, true, 512, JSON_THROW_ON_ERROR);

                return $decoded;
            }
        } catch (\Throwable) {
        }

        $payload = $this->buildSectionPayload($postId);
        try {
            Redis::setex($cacheKey, self::SECTION_CACHE_TTL_SECONDS, json_encode($payload, JSON_THROW_ON_ERROR));
        } catch (\Throwable) {
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSectionPayload(int $postId): array
    {
        $roots = $this->comments->getVisibleRootCommentsForPost($postId, self::ROOT_PAGE_SIZE);
        $stats = $this->comments->getVisibleSectionStats($postId);
        $rootIds = $roots->pluck('id')->map(fn (mixed $id): int => TypeCast::int($id))->all();
        $replyCounts = $this->comments->countVisibleRepliesByRootIds($rootIds);

        return [
            'roots' => $roots->map(fn (PostComment $comment): array => $this->serializeRootComment($comment))->all(),
            'total_roots' => $stats['totalRoots'],
            'total_visible' => $stats['totalVisible'],
            'reply_counts' => $replyCounts->all(),
            'reaction_aggregates' => $this->commentReactions->aggregateCountsForComments($rootIds),
        ];
    }

    /**
     * @param  list<int>  $commentIds
     * @param  array<int, array<string, int>>  $aggregates
     */
    private function buildReactionSummaries(array $commentIds, array $aggregates, ?int $userId): Collection
    {
        if ($commentIds === []) {
            return collect();
        }

        $userReactions = $userId !== null
            ? $this->commentReactions->userReactionsForComments($commentIds, $userId)
            : [];

        $summaries = collect();
        foreach ($commentIds as $commentId) {
            $counts = collect();
            foreach (ReactionType::all() as $type) {
                $counts->put($type->value, TypeCast::int($aggregates[$commentId][$type->value] ?? 0));
            }
            $summaries->put($commentId, new CommentReactionSummary(
                counts: $counts,
                userReaction: $userReactions[$commentId] ?? null,
            ));
        }

        return $summaries;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeRootComment(PostComment $comment): array
    {
        $user = $comment->relationLoaded('user') ? $comment->user : null;

        return [
            'id' => $comment->id,
            'post_id' => $comment->post_id,
            'user_id' => $comment->user_id,
            'body' => $comment->body,
            'created_at' => $comment->created_at?->toJSON(),
            'user' => $user instanceof User ? [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_path' => $user->avatar_path,
            ] : null,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return Collection<int, PostComment>
     */
    private function hydrateRootComments(array $rows): Collection
    {
        return collect($rows)->map(function (array $row): PostComment {
            /** @var array<string, mixed>|null $userData */
            $userData = isset($row['user']) && is_array($row['user']) ? $row['user'] : null;
            unset($row['user']);
            $comment = (new PostComment)->forceFill($row);
            $comment->exists = true;
            if (isset($row['created_at']) && is_string($row['created_at'])) {
                $comment->setAttribute('created_at', Carbon::parse($row['created_at']));
            }
            if ($userData !== null) {
                $user = (new User)->forceFill($userData);
                $user->exists = true;
                $comment->setRelation('user', $user);
            }

            return $comment;
        });
    }

    private function forgetSectionCache(int $postId): void
    {
        try {
            Redis::del(ApplicationCacheKeys::postCommentSection($postId));
        } catch (\Throwable) {
        }

        $this->engagementVersions->bump($postId);
    }

    /**
     * @return array{threadRootId: ?int, replyToId: ?int}
     */
    private function resolveReplyPlacement(CommentData $data): array
    {
        if ($data->parentId === null) {
            return ['threadRootId' => null, 'replyToId' => null];
        }

        $target = $this->comments->findById($data->parentId);
        if ($target === null) {
            throw InvalidCommentException::parentNotFound();
        }
        if ($target->post_id !== $data->postId) {
            throw InvalidCommentException::parentMismatch();
        }

        $threadRootId = $target->isRoot() ? $target->id : $target->parent_id;

        return ['threadRootId' => $threadRootId, 'replyToId' => $target->id];
    }
}
