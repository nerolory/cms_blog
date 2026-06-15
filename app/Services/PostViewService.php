<?php

namespace App\Services;

use App\DTO\PostEngagementData;
use App\Models\Post;
use App\Repositories\Contracts\CommentRepositoryContract;
use App\Repositories\Contracts\PostViewRepositoryContract;
use App\Repositories\Contracts\ReactionRepositoryContract;
use App\Services\Contracts\PostViewServiceContract;

/**
 * Сервис post view.

 *
 * @property-read PostViewRepositoryContract $views
 * @property-read CommentRepositoryContract $comments
 * @property-read ReactionRepositoryContract $reactions
 */
class PostViewService implements PostViewServiceContract
{
    public function __construct(protected PostViewRepositoryContract $views,
        protected CommentRepositoryContract $comments, protected ReactionRepositoryContract $reactions) {}

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
            rootComments: $this->comments->getVisibleRootCommentsForPost($post->id),
            reactionCounts: $reactions->counts,
            userReaction: $reactions->userReaction,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function flushPendingCounts(): void
    {
        $this->views->flushPendingCounts();
    }
}
