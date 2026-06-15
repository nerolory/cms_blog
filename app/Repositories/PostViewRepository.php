<?php

namespace App\Repositories;

use App\Models\Post;
use App\Repositories\Contracts\PostViewRepositoryContract;
use App\Support\TypeCast;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

/**
 * Репозиторий post view.
 *
 * @property-read Post $post
 */
class PostViewRepository implements PostViewRepositoryContract
{
    private const REDIS_KEY = 'post_views';

    public function __construct(protected Post $post) {}

    /**
     * increment.
     *
     * @param  int  $postId  id

     * @return int
     */
    public function increment(int $postId): int
    {
        try {
            $count = (int) Redis::hincrby(self::REDIS_KEY, (string) $postId, 1);
        } catch (\Throwable) {
            $this->post->newQuery()->whereKey($postId)->increment('views_count');

            return TypeCast::int($this->post->newQuery()->whereKey($postId)->value('views_count'));
        }

        return $count;
    }

    /**
     * Возвращает count.
     *
     * @param  int  $postId  id

     * @return int
     */
    public function getCount(int $postId): int
    {
        try {
            $pending = Redis::hget(self::REDIS_KEY, (string) $postId);
        } catch (\Throwable) {
            $pending = null;
        }
        $base = TypeCast::int($this->post->newQuery()->whereKey($postId)->value('views_count') ?? 0);
        if ($pending === null || $pending === false) {
            return $base;
        }

        return $base + TypeCast::int($pending);
    }

    /**
     * flush pending counts.
     */
    /**
     * Сбрасывает отложенные счётчики просмотров.
     *
     * @return Collection<int, int>
     */
    public function flushPendingCounts(): Collection
    {
        try {
            $pending = Redis::hgetall(self::REDIS_KEY);
        } catch (\Throwable) {
            return collect();
        }
        if ($pending === [] || $pending === false) {
            return collect();
        }
        $flushed = collect();
        foreach ($pending as $postId => $increment) {
            $id = TypeCast::int($postId);
            $delta = TypeCast::int($increment);
            if ($delta <= 0) {
                continue;
            }
            $this->post->newQuery()->whereKey($id)->increment('views_count', $delta);
            Redis::hdel(self::REDIS_KEY, (string) $id);
            $flushed->put($id, $delta);
        }

        return $flushed;
    }
}
