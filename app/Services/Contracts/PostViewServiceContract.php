<?php

namespace App\Services\Contracts;

use App\DTO\PostEngagementData;
use App\Models\Post;

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
     * Сбрасывает накопленные в Redis счётчики просмотров в БД.
     */
    public function flushPendingCounts(): void;
}
