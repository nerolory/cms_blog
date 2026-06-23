<?php

namespace App\Services\Contracts;

use App\DTO\PostEngagementData;
use App\DTO\PostListEngagementItem;
use App\Models\Post;
use Illuminate\Support\Collection;

/**
 * Контракт сервиса post view.
 */
interface PostViewServiceContract
{
    /**
     * record view.
     *
     * @param  Post  $post  пост

     * @return int
     */
    public function recordView(Post $post): int;

    /**
     * Возвращает engagement.
     *
     * @param  Post  $post  пост
     * @param  ?int  $userId  id
     * @param  ?int  $viewsCount  Уже известное число просмотров (после recordView)

     * @return PostEngagementData
     */
    public function getEngagement(Post $post, ?int $userId, ?int $viewsCount = null): PostEngagementData;

    /**
     * Сводка просмотров и реакций для карточек в списке постов.
     *
     * @param  Collection<int, int>  $postIds
     * @return Collection<int, PostListEngagementItem>
     */
    public function getListingEngagementForPostIds(Collection $postIds): Collection;

    /**
     * Сбрасывает накопленные в Redis счётчики просмотров в БД.
     */
    public function flushPendingCounts(): void;
}
