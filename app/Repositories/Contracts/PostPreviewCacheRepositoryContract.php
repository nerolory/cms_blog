<?php

namespace App\Repositories\Contracts;

use App\DTO\PostPreviewData;

/**
 * Контракт репозитория post preview cache.
 */
interface PostPreviewCacheRepositoryContract
{
    /**
     * store.

     *
     * @return bool
     */
    public function store(PostPreviewData $preview): bool;

    /**
     * Находит .

     *
     * @return ?PostPreviewData
     */
    public function find(string $token): ?PostPreviewData;

    /**
     * forget.

     *
     * @return bool
     */
    public function forget(string $token): bool;

    /**
     * Removes all cached previews and temporary media for the user.

     *
     * @return int
     */
    public function purgeForUser(int $userId): int;
}
