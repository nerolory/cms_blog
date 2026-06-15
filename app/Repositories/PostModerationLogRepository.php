<?php

namespace App\Repositories;

use App\Enums\PostModerationAction;
use App\Models\Post;
use App\Models\PostModerationLog;
use App\Models\User;
use App\Repositories\Contracts\PostModerationLogRepositoryContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Репозиторий post moderation log.

 *
 * @property-read PostModerationLog $log
 */
class PostModerationLogRepository implements PostModerationLogRepositoryContract
{
    public function __construct(protected PostModerationLog $log) {}

    /**
     * {@inheritdoc}
     *
     * @return Collection<int, PostModerationLog>
     */
    public function forPost(Post $post): Collection
    {
        return $this->log->newQuery()->with('actor')->where('post_id', $post->id)->orderByDesc('created_at')->get();
    }

    /**
     * {@inheritdoc}
     *
     * @param  SupportCollection<string, mixed>|null  $metadata
     * @return PostModerationLog
     */
    public function record(Post $post, PostModerationAction $action, ?User $actor = null, ?string $reason = null,
        ?SupportCollection $metadata = null): PostModerationLog
    {
        return $this->log->create(['post_id' => $post->id, 'actor_id' => $actor?->id, 'action' => $action,
            'reason' => $reason, 'metadata' => $metadata?->all()]);
    }
}
