<?php

namespace App\Repositories\Contracts;

use App\DTO\SearchFilters;
use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Контракт репозитория search.
 */
interface SearchRepositoryContract
{
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
     * index post.
     *
     * @param  Post  $post  пост
     */
    public function indexPost(Post $post): void;

    /**
     * remove post.
     *
     * @param  Post  $post  пост
     */
    public function removePost(Post $post): void;
}
