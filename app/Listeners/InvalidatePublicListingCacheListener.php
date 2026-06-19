<?php

namespace App\Listeners;

use App\Events\PostPublished;
use App\Events\PostUpdated;
use App\Repositories\Contracts\SeoRepositoryContract;

/**
 * Сбрасывает кэш публичного листинга при изменении постов.

 *
 * @property-read SeoRepositoryContract $seoRepository
 */
final class InvalidatePublicListingCacheListener
{
    public function __construct(protected SeoRepositoryContract $seoRepository) {}

    /**
     * Сбрасывает кэш timestamp листинга после изменения поста.
     *
     * @param  PostPublished|PostUpdated  $event
     * @return bool
     */
    public function handle(PostPublished|PostUpdated $event): bool
    {
        $this->seoRepository->forgetListingTimestampCache();

        return true;
    }
}
