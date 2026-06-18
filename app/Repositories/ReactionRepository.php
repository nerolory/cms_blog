<?php

namespace App\Repositories;

use App\DTO\ReactionData;
use App\DTO\ReactionEngagementData;
use App\Enums\ReactionType;
use App\Models\PostReaction;
use App\Repositories\Contracts\ReactionRepositoryContract;
use App\Support\TypeCast;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

/**
 * Репозиторий reaction.
 *
 * @property-read PostReaction $reaction
 */
class ReactionRepository implements ReactionRepositoryContract
{
    private const REDIS_TOTALS_KEY = 'post_reactions:totals:v2';

    private const REDIS_USER_REACTIONS_KEY = 'post_reactions:user:%d:v1';

    private const USER_REACTION_MISS = '__none__';

    private static bool $legacyCachesPurged = false;

    public function __construct(protected PostReaction $reaction) {}

    /**
     * upsert.
     */
    public function upsert(ReactionData $data): PostReaction
    {
        $reaction = $this->reaction->newQuery()->updateOrCreate(['post_id' => $data->postId, 'user_id' => $data->userId],
            ['type' => $data->type]);
        $this->refreshPostTotalsInRedis($data->postId);
        $this->rememberUserReactionInRedis($data->postId, $data->userId, $data->type);

        return $reaction;
    }

    /**
     * remove.
     */
    public function remove(int $postId, int $userId): bool
    {
        $removed = $this->reaction->newQuery()->where('post_id', $postId)->where('user_id', $userId)->delete() > 0;
        if ($removed) {
            $this->refreshPostTotalsInRedis($postId);
            $this->forgetUserReactionInRedis($postId, $userId);
        }

        return $removed;
    }

    /**
     * Возвращает счётчики реакций для поста.
     *
     * @return Collection<int, int>
     */
    public function countsForPost(int $postId): Collection
    {
        return $this->countsForPosts([$postId])->get($postId, collect());
    }

    /**
     * {@inheritdoc}
     */
    public function countsForPosts(array $postIds): Collection
    {
        if ($postIds === []) {
            return collect();
        }

        $this->purgeLegacyCachesOnce();

        /** @var array<int, array<string, int>> $arrays */
        $arrays = $this->resolveCountArraysForPosts($postIds);

        return $this->mapArraysToCollections($postIds, $arrays);
    }

    /**
     * user reaction.
     */
    public function userReaction(int $postId, int $userId): ?string
    {
        try {
            $cached = Redis::hget($this->userReactionsKey($userId), (string) $postId);
            if ($cached === false) {
                return $this->loadUserReactionFromDatabase($postId, $userId);
            }
            if ($cached === self::USER_REACTION_MISS || $cached === '') {
                return null;
            }

            return TypeCast::string($cached);
        } catch (\Throwable) {
            return $this->loadUserReactionFromDatabase($postId, $userId);
        }
    }

    private function loadUserReactionFromDatabase(int $postId, int $userId): ?string
    {
        $reaction = $this->reaction->newQuery()->where('post_id', $postId)->where('user_id', $userId)->first();
        $type = null;
        if ($reaction !== null) {
            $type = $reaction->type instanceof ReactionType ? $reaction->type->value : (is_string($reaction
                ->type) ? $reaction->type : null);
        }
        $this->rememberUserReactionInRedis($postId, $userId, $type);

        return $type;
    }

    private function rememberUserReactionInRedis(int $postId, int $userId, ?string $type): void
    {
        try {
            Redis::hset(
                $this->userReactionsKey($userId),
                (string) $postId,
                $type ?? self::USER_REACTION_MISS,
            );
        } catch (\Throwable) {
        }
    }

    private function forgetUserReactionInRedis(int $postId, int $userId): void
    {
        try {
            Redis::hdel($this->userReactionsKey($userId), (string) $postId);
        } catch (\Throwable) {
        }
    }

    private function userReactionsKey(int $userId): string
    {
        return sprintf(self::REDIS_USER_REACTIONS_KEY, $userId);
    }

    /**
     * {@inheritdoc}
     */
    public function countsAndUserReactionForPost(int $postId, ?int $userId): ReactionEngagementData
    {
        $aggregates = $this->countsForPosts([$postId])->get($postId, collect());
        $counts = collect();
        foreach (ReactionType::all() as $type) {
            $counts->put($type->value, TypeCast::int($aggregates->get($type->value, 0)));
        }
        $userReaction = $userId !== null ? $this->userReaction($postId, $userId) : null;

        return new ReactionEngagementData(counts: $counts, userReaction: $userReaction);
    }

    /**
     * @param  list<int>  $postIds
     * @return array<int, array<string, int>>
     */
    private function resolveCountArraysForPosts(array $postIds): array
    {
        try {
            return $this->getCountArraysFromRedis($postIds);
        } catch (\Throwable) {
            return $this->loadCountArraysFromDatabase($postIds);
        }
    }

    /**
     * @param  list<int>  $postIds
     * @return array<int, array<string, int>>
     */
    private function loadCountArraysFromDatabase(array $postIds): array
    {
        $rows = $this->reaction->newQuery()->selectRaw('post_id, type, COUNT(*) as aggregate')->whereIn('post_id',
            $postIds)->groupBy('post_id', 'type')->get();
        $aggregatesByPost = [];
        foreach ($rows as $row) {
            $postId = TypeCast::int($row->post_id);
            $type = $row->type instanceof ReactionType ? $row->type->value : TypeCast::string($row->type);
            $aggregatesByPost[$postId][$type] = TypeCast::int($row->aggregate);
        }

        $result = [];
        foreach ($postIds as $postId) {
            $counts = [];
            foreach (ReactionType::all() as $type) {
                $count = TypeCast::int($aggregatesByPost[$postId][$type->value] ?? 0);
                if ($count > 0) {
                    $counts[$type->value] = $count;
                }
            }
            $result[$postId] = $counts;
        }

        return $result;
    }

    /**
     * @param  list<int>  $postIds
     * @return array<int, array<string, int>>
     */
    private function getCountArraysFromRedis(array $postIds): array
    {
        $stringIds = array_map(strval(...), $postIds);
        $cachedTotals = Redis::hmget(self::REDIS_TOTALS_KEY, $stringIds);
        $missingIds = [];
        foreach ($postIds as $index => $postId) {
            $cached = $cachedTotals[$index] ?? false;
            if ($cached === false || $cached === null || $cached === '') {
                $missingIds[] = $postId;
            }
        }
        if ($missingIds !== []) {
            $this->hydrateTotalsFromDatabase($missingIds);
            $cachedTotals = Redis::hmget(self::REDIS_TOTALS_KEY, $stringIds);
        }

        $result = [];
        $corruptIds = [];
        foreach ($postIds as $index => $postId) {
            try {
                $result[$postId] = $this->normalizePayloadToArray($cachedTotals[$index] ?? null);
            } catch (\Throwable) {
                $corruptIds[] = $postId;
                Redis::hdel(self::REDIS_TOTALS_KEY, (string) $postId);
            }
        }
        if ($corruptIds !== []) {
            $this->hydrateTotalsFromDatabase($corruptIds);
            $refreshed = Redis::hmget(self::REDIS_TOTALS_KEY, array_map(strval(...), $corruptIds));
            foreach ($corruptIds as $index => $postId) {
                $result[$postId] = $this->normalizePayloadToArray($refreshed[$index] ?? null);
            }
        }

        return $result;
    }

    /**
     * @param  list<int>  $postIds
     */
    private function hydrateTotalsFromDatabase(array $postIds): void
    {
        $countsByPost = $this->loadCountArraysFromDatabase($postIds);
        foreach ($postIds as $postId) {
            $encoded = json_encode($countsByPost[$postId] ?? [], JSON_THROW_ON_ERROR);
            Redis::hset(self::REDIS_TOTALS_KEY, (string) $postId, $encoded);
        }
    }

    private function refreshPostTotalsInRedis(int $postId): void
    {
        try {
            $this->hydrateTotalsFromDatabase([$postId]);
        } catch (\Throwable) {
            Redis::hdel(self::REDIS_TOTALS_KEY, (string) $postId);
        }
    }

    /**
     * @param  list<int>  $postIds
     * @param  array<int, array<string, int>>  $arrays
     * @return Collection<int, Collection<int, int>>
     */
    private function mapArraysToCollections(array $postIds, array $arrays): Collection
    {
        $countsByPost = collect();
        foreach ($postIds as $postId) {
            $counts = collect();
            foreach ($arrays[$postId] ?? [] as $type => $count) {
                $value = TypeCast::int($count);
                if ($value > 0) {
                    $counts->put(TypeCast::string($type), $value);
                }
            }
            $countsByPost->put($postId, $counts);
        }

        return $countsByPost;
    }

    /**
     * @return array<string, int>
     */
    private function normalizePayloadToArray(mixed $payload): array
    {
        if ($payload === null || $payload === false || $payload === '') {
            return [];
        }
        if (is_array($payload)) {
            return $this->filterPositiveCounts($payload);
        }
        if ($payload instanceof Collection) {
            return $this->filterPositiveCounts($payload->all());
        }
        if (! is_string($payload)) {
            throw new \RuntimeException('Unsupported reaction cache payload type.');
        }
        if ($this->isLegacySerializedPayload($payload)) {
            throw new \RuntimeException('Legacy serialized reaction cache payload.');
        }
        /** @var mixed $decoded */
        $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($decoded)) {
            return [];
        }

        return $this->filterPositiveCounts($decoded);
    }

    /**
     * @param  array<mixed, mixed>  $counts
     * @return array<string, int>
     */
    private function filterPositiveCounts(array $counts): array
    {
        $filtered = [];
        foreach ($counts as $type => $count) {
            $value = TypeCast::int($count);
            if ($value > 0) {
                $filtered[TypeCast::string($type)] = $value;
            }
        }

        return $filtered;
    }

    private function isLegacySerializedPayload(string $payload): bool
    {
        return str_starts_with($payload, 'a:') || str_starts_with($payload, 'O:') || str_starts_with($payload, 's:');
    }

    private function purgeLegacyCachesOnce(): void
    {
        if (self::$legacyCachesPurged) {
            return;
        }
        self::$legacyCachesPurged = true;

        try {
            Redis::del('post_reactions:totals');
        } catch (\Throwable) {
        }
    }
}
