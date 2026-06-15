<?php

namespace App\Repositories;

use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Post;
use App\Repositories\Contracts\FeedRepositoryContract;
use Illuminate\Support\Collection;

/**
 * Репозиторий feed.
 *
 * @property-read Post $post
 */
class FeedRepository implements FeedRepositoryContract
{
    public function __construct(protected Post $post) {}

    /**
     * Возвращает последние опубликованные посты для ленты.
     *
     * @return Collection<int, Post>
     */
    public function getLatestPublished(int $limit): Collection
    {
        return $this->post->newQuery()->with(['user', 'category'])->where('status',
            PostStatus::Published)->where('visibility',
                PostVisibility::Guest->value)->orderByDesc('published_at')->limit($limit)->get();
    }
}
