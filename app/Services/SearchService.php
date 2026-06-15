<?php

namespace App\Services;

use App\DTO\SearchFilters;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\PostRepositoryContract;
use App\Repositories\Contracts\SearchRepositoryContract;
use App\Services\Contracts\SearchServiceContract;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Сервис search.
 *
 * @property-read SearchRepositoryContract $searchRepository
 * @property-read PostRepositoryContract $postRepository
 */
class SearchService implements SearchServiceContract
{
    public function __construct(protected SearchRepositoryContract $searchRepository,
        protected PostRepositoryContract $postRepository) {}

    /**
     * search.
     */ /**
     * Выполняет поиск постов по фильтрам.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function search(SearchFilters $filters, ?User $viewer): LengthAwarePaginator
    {
        return $this->searchRepository->search($filters, $viewer);
    }

    /**
     * highlight.

     *
     * @return string
     */
    public function highlight(string $text, string $query): string
    {
        if ($query === '') {
            return e($text);
        }
        $pattern = '/('.preg_quote($query, '/').')/iu';

        return preg_replace($pattern, '<mark>$1</mark>', e($text)) ?? e($text);
    }

    /**
     * index post.
     *
     * @param  Post  $post  пост
     */
    public function indexPost(Post $post): void
    {
        $this->searchRepository->indexPost($post);
    }

    /**
     * {@inheritdoc}
     */
    public function indexPublishedPostById(int $postId): void
    {
        $post = $this->postRepository->findByIdOrNull($postId);
        if ($post === null || $post->status !== PostStatus::Published) {
            return;
        }
        $this->indexPost($post);
    }
}
