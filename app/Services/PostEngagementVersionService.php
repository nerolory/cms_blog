<?php

namespace App\Services;

use App\Services\Contracts\PostEngagementVersionServiceContract;
use App\Support\Cache\ApplicationCacheKeys;
use Illuminate\Support\Facades\Redis;

/**
 * Версия engagement-блока поста в Redis.
 */
class PostEngagementVersionService implements PostEngagementVersionServiceContract
{
    public function get(int $postId): string
    {
        try {
            $value = Redis::get(ApplicationCacheKeys::postEngagementVersion($postId));
        } catch (\Throwable) {
            return '0';
        }

        return $value !== null && $value !== false ? (string) $value : '0';
    }

    public function bump(int $postId): string
    {
        try {
            return (string) Redis::incr(ApplicationCacheKeys::postEngagementVersion($postId));
        } catch (\Throwable) {
            return $this->get($postId);
        }
    }
}
