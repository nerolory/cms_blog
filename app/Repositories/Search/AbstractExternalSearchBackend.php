<?php

namespace App\Repositories\Search;

use App\DTO\SearchFilters;
use App\Enums\SearchDriver;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Search\Contracts\SearchBackendContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * Base class for optional external search engines (enabled via config + compose profiles).
 *
 * @property-read DatabaseSearchBackend $databaseFallback
 */
abstract class AbstractExternalSearchBackend implements SearchBackendContract
{
    public function __construct(protected DatabaseSearchBackend $databaseFallback) {}

    /**
     * driver.

     *
     * @return SearchDriver
     */
    abstract public function driver(): SearchDriver;

    /**
     * Проверяет available.

     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return (bool) config('search.backends.'.$this->driver()->value.'.enabled', false);
    }

    /**
     * search.
     */ /**
     * Выполняет поиск постов по фильтрам.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function search(SearchFilters $filters, ?User $viewer): LengthAwarePaginator
    {
        return $this->databaseFallback->search($filters, $viewer);
    }

    /**
     * index.
     *
     * @param  Post  $post  пост
     */
    public function index(Post $post): void
    {
        Log::debug('Search index stub invoked.', ['driver' => $this->driver()->value, 'post_id' => $post->id]);
    }

    /**
     * remove.
     *
     * @param  Post  $post  пост
     */
    public function remove(Post $post): void
    {
        Log::debug('Search remove stub invoked.', ['driver' => $this->driver()->value, 'post_id' => $post->id]);
    }
}
