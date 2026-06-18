<?php

namespace App\Repositories;

use App\Enums\AiResultStatus;
use App\Enums\AiToolCode;
use App\Models\AiToolResult;
use App\Models\Post;
use App\Repositories\Contracts\AiToolResultRepositoryContract;
use App\Support\Cache\ApplicationCacheKeys;
use App\Support\TypeCast;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Redis;

/**
 * Репозиторий ai tool result.
 *
 * @property-read AiToolResult $result
 */
class AiToolResultRepository implements AiToolResultRepositoryContract
{
    private const CACHE_TTL_SECONDS = 3600;

    public function __construct(protected AiToolResult $result) {}

    /**
     * {@inheritdoc}
     *
     * @return Collection<int, AiToolResult>
     */
    public function getCompletedForPost(Post $post): Collection
    {
        $payload = $this->resolveCompletedPayload($post->id);
        if ($payload === []) {
            return new Collection;
        }

        return $this->hydrateCompletedResults($payload);
    }

    /**
     * upsert for post.
     */
    public function upsertForPost(Post $post, AiToolCode $toolCode, AiResultStatus $status, string $summary,
        ?string $detail = null): AiToolResult
    {
        $payload = ['summary' => $summary];
        if ($detail !== null) {
            $payload['detail'] = $detail;
        }

        $result = $this->result->newQuery()->updateOrCreate(['subject_type' => Post::class, 'subject_id' => $post->id,
            'tool_code' => $toolCode], ['status' => $status, 'payload' => $payload,
                'metadata' => ['source' => 'demo_execution'],
                'completed_at' => $status === AiResultStatus::Completed ? now() : null]);
        $this->forgetCompletedCache($post->id);

        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function resolveCompletedPayload(int $postId): array
    {
        $cacheKey = ApplicationCacheKeys::postAiToolResults($postId);
        try {
            $cached = Redis::get($cacheKey);
            if (is_string($cached) && $cached !== '') {
                /** @var list<array<string, mixed>> $decoded */
                $decoded = json_decode($cached, true, 512, JSON_THROW_ON_ERROR);

                return $decoded;
            }
        } catch (\Throwable) {
        }

        $payload = $this->loadCompletedPayloadFromDatabase($postId);
        try {
            Redis::setex($cacheKey, self::CACHE_TTL_SECONDS, json_encode($payload, JSON_THROW_ON_ERROR));
        } catch (\Throwable) {
        }

        return $payload;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function loadCompletedPayloadFromDatabase(int $postId): array
    {
        return $this->result->newQuery()
            ->where('subject_type', Post::class)
            ->where('subject_id', $postId)
            ->where('status', AiResultStatus::Completed)
            ->orderByDesc('completed_at')
            ->get()
            ->map(fn (AiToolResult $result): array => [
                'id' => $result->id,
                'tool_code' => $result->tool_code instanceof AiToolCode ? $result->tool_code->value : (string) $result->tool_code,
                'payload' => TypeCast::array($result->payload),
                'completed_at' => $result->completed_at?->toJSON(),
            ])
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $payload
     * @return Collection<int, AiToolResult>
     */
    private function hydrateCompletedResults(array $payload): Collection
    {
        $results = [];
        foreach ($payload as $row) {
            $result = (new AiToolResult)->forceFill([
                'id' => $row['id'] ?? null,
                'tool_code' => $row['tool_code'] ?? null,
                'payload' => $row['payload'] ?? [],
                'completed_at' => isset($row['completed_at']) && is_string($row['completed_at'])
                    ? $row['completed_at']
                    : null,
                'status' => AiResultStatus::Completed,
            ]);
            $result->exists = true;
            $results[] = $result;
        }

        return new Collection($results);
    }

    private function forgetCompletedCache(int $postId): void
    {
        try {
            Redis::del(ApplicationCacheKeys::postAiToolResults($postId));
        } catch (\Throwable) {
        }
    }
}
