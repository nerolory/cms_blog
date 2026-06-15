<?php

namespace App\Services\Contracts;

use App\DTO\SearchFilters;
use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Контракт сервиса search.
 */
interface SearchServiceContract
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
     * highlight.

     *
     * @return string
     */
    public function highlight(string $text, string $query): string;

    /**
     * index post.
     *
     * @param  Post  $post  пост
     */
    public function indexPost(Post $post): void;

    /**
     * Индексирует published-пост по id; no-op, если пост не найден.
     */
    public function indexPublishedPostById(int $postId): void;
}
