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
    private const REDIS_PENDING_KEY = 'post_views';

    private const REDIS_TOTALS_KEY = 'post_views:totals';

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
            $count = $this->incrementDatabase($postId);
            Redis::hset(self::REDIS_TOTALS_KEY, (string) $postId, (string) $count);
            Redis::hdel(self::REDIS_PENDING_KEY, (string) $postId);

            return $count;
        } catch (\Throwable) {
            return $this->incrementDatabase($postId);
        }
    }

    /**
     * Возвращает count.
     *
     * @param  int  $postId  id

     * @return int
     */
    public function getCount(int $postId): int
    {
        $counts = $this->getCountsForPosts([$postId]);

        return TypeCast::int($counts->get($postId, 0));
    }

    /**
     * {@inheritdoc}
     */
    public function getCountsForPosts(array $postIds): Collection
    {
        if ($postIds === []) {
            return collect();
        }

        try {
            return $this->getCountsForPostsFromRedis($postIds);
        } catch (\Throwable) {
            return $this->getCountsForPostsFromDatabase($postIds);
        }
    }

    /**
     * Сбрасывает отложенные счётчики просмотров (legacy pending до write-through).
     *
     * @return Collection<int, int>
     */
    public function flushPendingCounts(): Collection
    {
        try {
            $pending = Redis::hgetall(self::REDIS_PENDING_KEY);
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
            Redis::hdel(self::REDIS_PENDING_KEY, (string) $id);
            $flushed->put($id, $delta);
            $this->syncTotalsFromDatabase($id);
        }

        return $flushed;
    }

    /**
     * @param  list<int>  $postIds
     * @return Collection<int, int>
     */
    private function getCountsForPostsFromRedis(array $postIds): Collection
    {
        $stringIds = array_map(strval(...), $postIds);
        $cachedTotals = Redis::hmget(self::REDIS_TOTALS_KEY, $stringIds);
        $missingIds = [];
        foreach ($postIds as $index => $postId) {
            $cached = $cachedTotals[$index] ?? false;
            if ($cached === false || $cached === null) {
                $missingIds[] = $postId;
            }
        }
        if ($missingIds !== []) {
            $this->hydrateTotalsFromDatabase($missingIds);
            $cachedTotals = Redis::hmget(self::REDIS_TOTALS_KEY, $stringIds);
        }

        $dbCounts = $this->getCountsForPostsFromDatabase($postIds);
        $counts = collect();
        foreach ($postIds as $index => $postId) {
            $redisCount = TypeCast::int($cachedTotals[$index] ?? 0);
            $dbCount = TypeCast::int($dbCounts->get($postId, 0));
            $count = max($redisCount, $dbCount);
            if ($count > $redisCount) {
                Redis::hset(self::REDIS_TOTALS_KEY, (string) $postId, (string) $count);
            }
            $counts->put($postId, $count);
        }

        return $counts;
    }

    /**
     * @param  list<int>  $postIds
     * @return Collection<int, int>
     */
    private function getCountsForPostsFromDatabase(array $postIds): Collection
    {
        $baseCounts = $this->post->newQuery()->whereIn('id', $postIds)->pluck('views_count', 'id');
        $counts = collect();
        foreach ($postIds as $postId) {
            $counts->put($postId, TypeCast::int($baseCounts[$postId] ?? 0));
        }

        return $counts;
    }

    /**
     * @param  list<int>  $postIds
     */
    private function hydrateTotalsFromDatabase(array $postIds): void
    {
        $baseCounts = $this->post->newQuery()->whereIn('id', $postIds)->pluck('views_count', 'id');
        foreach ($postIds as $postId) {
            $base = TypeCast::int($baseCounts[$postId] ?? 0);
            $pending = $this->pendingIncrementFor($postId);
            Redis::hset(self::REDIS_TOTALS_KEY, (string) $postId, (string) ($base + $pending));
        }
    }

    private function incrementDatabase(int $postId): int
    {
        $this->post->newQuery()->whereKey($postId)->increment('views_count');

        return TypeCast::int($this->post->newQuery()->whereKey($postId)->value('views_count'));
    }

    private function syncTotalsFromDatabase(int $postId): void
    {
        $count = TypeCast::int($this->post->newQuery()->whereKey($postId)->value('views_count') ?? 0);
        Redis::hset(self::REDIS_TOTALS_KEY, (string) $postId, (string) $count);
    }

    private function pendingIncrementFor(int $postId): int
    {
        try {
            $pending = Redis::hget(self::REDIS_PENDING_KEY, (string) $postId);
        } catch (\Throwable) {
            return 0;
        }
        if ($pending === null || $pending === false) {
            return 0;
        }

        return TypeCast::int($pending);
    }
}
