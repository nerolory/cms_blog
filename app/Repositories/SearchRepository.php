<?php

namespace App\Repositories;

use App\DTO\SearchFilters;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\SearchRepositoryContract;
use App\Services\Search\SearchBackendResolver;
use App\Support\Cache\CacheVersionManager;
use App\Support\Cache\EloquentCache;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Репозиторий search.
 *
 * @property-read SearchBackendResolver $backendResolver
 * @property-read CacheVersionManager $cacheVersions
 */
class SearchRepository implements SearchRepositoryContract
{
    public function __construct(protected SearchBackendResolver $backendResolver,
        protected CacheVersionManager $cacheVersions) {}

    /**
     * Выполняет поиск постов по фильтрам.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function search(SearchFilters $filters, ?User $viewer): LengthAwarePaginator
    {
        $viewerKey = $viewer !== null ? (string) $viewer->id : 'guest';
        $driverKey = $this->backendResolver->resolvedDriver()->value;
        $cacheKey = sprintf('search.v2.%s.%s.%s.%s', $driverKey, $this->cacheVersions->get(CacheVersionManager::SEARCH),
            $viewerKey, $filters->cacheKeySuffix());

        return EloquentCache::rememberPaginator($cacheKey, now()->addMinutes(15),
            fn (): LengthAwarePaginator => $this->backendResolver->resolve()->search($filters, $viewer));
    }

    /**
     * index post.
     *
     * @param  Post  $post  пост
     */
    public function indexPost(Post $post): void
    {
        $this->backendResolver->resolve()->index($post);
    }

    /**
     * remove post.
     *
     * @param  Post  $post  пост
     */
    public function removePost(Post $post): void
    {
        $this->backendResolver->resolve()->remove($post);
    }
}
