<?php

namespace App\Repositories\Contracts;

use App\Enums\PostModerationAction;
use App\Models\Post;
use App\Models\PostModerationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Контракт репозитория post moderation log.
 */
interface PostModerationLogRepositoryContract
{
    /**
     * Возвращает записи журнала модерации поста.
     *
     * @return Collection<int, PostModerationLog>
     */
    public function forPost(Post $post): Collection;

    /**
     * Сохраняет запись журнала модерации.
     *
     * @param  SupportCollection<int, mixed>|null  $metadata
     * @return PostModerationLog
     */
    public function record(Post $post, PostModerationAction $action, ?User $actor = null, ?string $reason = null,
        ?SupportCollection $metadata = null): PostModerationLog;
}
