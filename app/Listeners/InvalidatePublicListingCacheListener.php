<?php

namespace App\Listeners;

use App\Events\PostPublished;
use App\Events\PostUpdated;
use App\Repositories\Contracts\SeoRepositoryContract;

/**
 * Сбрасывает кэш публичного листинга при изменении постов.
 */
final class InvalidatePublicListingCacheListener
{
    public function __construct(protected SeoRepositoryContract $seoRepository) {}

    public function handle(PostPublished|PostUpdated $event): bool
    {
        $this->seoRepository->forgetListingTimestampCache();

        return true;
    }
}
