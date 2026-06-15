<?php

namespace App\Repositories\Search;

use App\DTO\SearchFilters;
use App\Enums\PostStatus;
use App\Enums\SearchDriver;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Search\Contracts\SearchBackendContract;
use App\Support\Post\VisibilityChecker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Репозиторий database search backend.
 *
 * @property-read Post $post
 * @property-read VisibilityChecker $visibilityChecker
 */
class DatabaseSearchBackend implements SearchBackendContract
{
    public function __construct(protected Post $post, protected VisibilityChecker $visibilityChecker) {}

    /**
     * driver.

     *
     * @return SearchDriver
     */
    public function driver(): SearchDriver
    {
        return SearchDriver::Database;
    }

    /**
     * Проверяет available.

     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * Выполняет поиск постов по фильтрам.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function search(SearchFilters $filters, ?User $viewer): LengthAwarePaginator
    {
        $query = $this->post->newQuery()->with(['user', 'category', 'tags'])->where('status', PostStatus::Published)
            ->where(fn (Builder $builder) => $this->visibilityChecker->applyPublicListingScope($builder, $viewer));
        if ($filters->categoryId !== null) {
            $query->where('category_id', $filters->categoryId);
        }
        if ($filters->authorId !== null) {
            $query->where('user_id', $filters->authorId);
        }
        if ($filters->dateFrom !== null) {
            $query->whereDate('published_at', '>=', $filters->dateFrom);
        }
        if ($filters->dateTo !== null) {
            $query->whereDate('published_at', '<=', $filters->dateTo);
        }
        if ($filters->tagIds->isNotEmpty()) {
            $tagIds = $filters->tagIds->all();
            $query->whereHas('tags', fn (Builder $tagQuery) => $tagQuery->whereIn('tags.id', $tagIds));
        }
        if ($filters->hasQuery()) {
            $this->applyFullTextSearch($query, $filters->query);
        } else {
            $query->orderByDesc('published_at');
        }

        return $query->paginate($filters->perPage, ['*'], 'page', $filters->page);
    }

    /**
     * index.
     *
     * @param  Post  $post  пост
     */
    public function index(Post $post): void
    {
        // Published posts are already stored in the database.
    }

    /**
     * remove.
     *
     * @param  Post  $post  пост
     */
    public function remove(Post $post): void
    {
        // No external index to update.
    }

    /**
     * @param  Builder<Post>  $query
     */
    private function applyFullTextSearch(Builder $query, string $term): void
    {
        if (DB::getDriverName() === 'pgsql') {
            $query->whereRaw("search_vector @@ plainto_tsquery('simple', ?)",
                [$term])->orderByRaw("ts_rank(search_vector, plainto_tsquery('simple', ?)) DESC", [$term]);

            return;
        }
        $like = '%'.$term.'%';
        $query->where(function (Builder $builder) use ($like): void {
            $builder->where('title', 'like', $like)->orWhere('excerpt', 'like', $like);
        })->orderByDesc('published_at');
    }
}
