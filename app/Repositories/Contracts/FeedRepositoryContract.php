<?php

namespace App\Repositories\Contracts;

use App\Models\Post;
use Illuminate\Support\Collection;

/**
 * Контракт репозитория feed.
 */
interface FeedRepositoryContract
{
    /**
     * Возвращает последние опубликованные посты для ленты.
     *
     * @return Collection<int, Post>
     */
    public function getLatestPublished(int $limit): Collection;
}
