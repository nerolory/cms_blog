<?php

namespace App\Services;

use App\DTO\PostEngagementData;
use App\DTO\PostListEngagementItem;
use App\Models\Post;
use App\Repositories\Contracts\CommentRepositoryContract;
use App\Repositories\Contracts\PostViewRepositoryContract;
use App\Repositories\Contracts\ReactionRepositoryContract;
use App\Services\Contracts\CommentServiceContract;
use App\Services\Contracts\PostEngagementVersionServiceContract;
use App\Services\Contracts\PostViewServiceContract;
use App\Support\TypeCast;
use Illuminate\Support\Collection;

/**
 * Сервис post view.

 *
 * @property-read PostViewRepositoryContract $views
 * @property-read CommentRepositoryContract $comments
 * @property-read ReactionRepositoryContract $reactions

 * @property-read PostEngagementVersionServiceContract $engagementVersions
 */
class PostViewService implements PostViewServiceContract
{
    public function __construct(
        protected PostViewRepositoryContract $views,
        protected CommentServiceContract $comments,
        protected ReactionRepositoryContract $reactions,
        protected PostEngagementVersionServiceContract $engagementVersions,
    ) {}

    /**
     * record view.
     *
     * @param  Post  $post  пост

     * @return int
     */
    public function recordView(Post $post): int
    {
        return $this->views->increment($post->id);
    }

    /**
     * Возвращает engagement.
     *
     * @param  Post  $post  пост
     * @param  ?int  $userId  id

     * @return PostEngagementData
     */
    public function getEngagement(Post $post, ?int $userId, ?int $viewsCount = null): PostEngagementData
    {
        $reactions = $this->reactions->countsAndUserReactionForPost($post->id, $userId);

        return new PostEngagementData(
            viewsCount: $viewsCount ?? $this->views->getCount($post->id),
            comments: $this->comments->getSectionForPost($post->id, $userId),
            reactionCounts: $reactions->counts,
            userReaction: $reactions->userReaction,
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return Collection<int, PostListEngagementItem>
     */
    public function getListingEngagementForPostIds(Collection $postIds): Collection
    {
        if ($postIds->isEmpty()) {
            return collect();
        }

        $ids = $postIds->values()->all();
        $viewCounts = $this->views->getCountsForPosts($postIds);
        $reactionCounts = $this->reactions->countsForPosts($postIds);

        $items = collect();
        foreach ($ids as $postId) {
            $id = TypeCast::int($postId);
            $items->put($id, new PostListEngagementItem(
                viewsCount: TypeCast::int($viewCounts->get($id, 0)),
                reactionCounts: $reactionCounts->get($id, collect()),
            ));
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function flushPendingCounts(): void
    {
        $flushed = $this->views->flushPendingCounts();
        foreach ($flushed->keys() as $postId) {
            $this->engagementVersions->bump((int) $postId);
        }
    }
}
