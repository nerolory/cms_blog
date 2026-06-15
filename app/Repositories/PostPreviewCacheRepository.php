<?php

namespace App\Repositories;

use App\DTO\PostPreviewData;
use App\Repositories\Contracts\FileRepositoryContract;
use App\Repositories\Contracts\PostPreviewCacheRepositoryContract;
use App\Support\TypeCast;
use Illuminate\Support\Facades\Cache;

/**
 * Репозиторий post preview cache.

 *
 * @property-read FileRepositoryContract $fileRepository
 */
class PostPreviewCacheRepository implements PostPreviewCacheRepositoryContract
{
    public function __construct(protected FileRepositoryContract $fileRepository) {}

    /**
     * {@inheritdoc}

     *
     * @return bool
     */
    public function store(PostPreviewData $preview): bool
    {
        $ttlMinutes = max(1, TypeCast::int(config('post-preview.ttl_minutes', 30)));
        $ttl = now()->addMinutes($ttlMinutes);
        $cacheKey = $this->previewKey($preview->token);
        Cache::put($cacheKey, $preview->toCachePayload(), $ttl);
        /** @var list<string> $tokens */
        $tokens = Cache::get($this->userIndexKey($preview->userId), []);
        if (! in_array($preview->token, $tokens, true)) {
            $tokens[] = $preview->token;
            Cache::put($this->userIndexKey($preview->userId), $tokens, $ttl);
        }

        return true;
    }

    /**
     * {@inheritdoc}

     *
     * @return ?PostPreviewData
     */
    public function find(string $token): ?PostPreviewData
    {
        /** @var array<string, mixed>|null $payload */
        $payload = Cache::get($this->previewKey($token));
        if (! is_array($payload)) {
            return null;
        }

        return PostPreviewData::fromCachePayload($payload);
    }

    /**
     * {@inheritdoc}

     *
     * @return bool
     */
    public function forget(string $token): bool
    {
        $preview = $this->find($token);
        if ($preview !== null) {
            $this->deleteTemporaryMedia($preview);
            $this->removeTokenFromUserIndex($preview->userId, $token);
        }

        return Cache::forget($this->previewKey($token));
    }

    /**
     * {@inheritdoc}

     *
     * @return int
     */
    public function purgeForUser(int $userId): int
    {
        /** @var list<string> $tokens */
        $tokens = Cache::get($this->userIndexKey($userId), []);
        $removed = 0;
        foreach ($tokens as $token) {
            if (! is_string($token) || $token === '') {
                continue;
            }
            if ($this->forget($token)) {
                $removed++;
            }
        }
        Cache::forget($this->userIndexKey($userId));

        return $removed;
    }

    private function previewKey(string $token): string
    {
        return TypeCast::string(config('post-preview.cache_prefix', 'posts.preview.')).$token;
    }

    private function userIndexKey(int $userId): string
    {
        return TypeCast::string(config('post-preview.cache_prefix', 'posts.preview.')).'index.'.$userId;
    }

    private function removeTokenFromUserIndex(int $userId, string $token): void
    {
        /** @var list<string> $tokens */
        $tokens = Cache::get($this->userIndexKey($userId), []);
        $tokens = array_values(array_filter($tokens,
            fn (mixed $value): bool => is_string($value) && $value !== $token));
        if ($tokens === []) {
            Cache::forget($this->userIndexKey($userId));

            return;
        }
        $ttlMinutes = max(1, TypeCast::int(config('post-preview.ttl_minutes', 30)));
        Cache::put($this->userIndexKey($userId), $tokens, now()->addMinutes($ttlMinutes));
    }

    private function deleteTemporaryMedia(PostPreviewData $preview): void
    {
        foreach ($preview->temporaryMediaPaths as $path) {
            $this->fileRepository->deletePublic($path);
        }
    }
}
