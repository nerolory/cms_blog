<?php

namespace App\Services;

use App\DTO\SearchFilterOptions;
use App\Repositories\Contracts\CategoryRepositoryContract;
use App\Repositories\Contracts\TagRepositoryContract;
use App\Services\Contracts\SearchPageServiceContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Страница поиска: кэшированные справочники категорий и тегов.
 *
 * @property-read CategoryRepositoryContract $categories
 * @property-read TagRepositoryContract $tags
 */
class SearchPageService implements SearchPageServiceContract
{
    private const CACHE_TTL_SECONDS = 3600;

    public function __construct(protected CategoryRepositoryContract $categories,
        protected TagRepositoryContract $tags) {}

    /**
     * filter options.

     *
     * @return SearchFilterOptions
     */
    public function filterOptions(): SearchFilterOptions
    {
        return new SearchFilterOptions(categories: Cache::remember('search:categories', self::CACHE_TTL_SECONDS,
            fn (): Collection => $this->categories->allOrdered()), tags: Cache::remember('search:tags',
                self::CACHE_TTL_SECONDS, fn (): Collection => $this->tags->allOrdered()));
    }
}
