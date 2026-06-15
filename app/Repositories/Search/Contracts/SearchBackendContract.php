<?php

namespace App\Repositories\Search\Contracts;

use App\DTO\SearchFilters;
use App\Enums\SearchDriver;
use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Репозиторий search backend.
 */
interface SearchBackendContract
{
    /**
     * driver.

     *
     * @return SearchDriver
     */
    public function driver(): SearchDriver;

    /**
     * Проверяет available.

     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * search.
     */
    /**
     * search.
     */
    /**
     * Выполняет поиск постов по фильтрам.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function search(SearchFilters $filters, ?User $viewer): LengthAwarePaginator;

    /**
     * index.
     *
     * @param  Post  $post  пост
     */
    public function index(Post $post): void;

    /**
     * remove.
     *
     * @param  Post  $post  пост
     */
    public function remove(Post $post): void;
}
