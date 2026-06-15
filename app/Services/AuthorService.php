<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\AuthorRepositoryContract;
use App\Services\Contracts\AuthorServiceContract;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Сервис author.
 *
 * @property-read AuthorRepositoryContract $authors
 */
class AuthorService implements AuthorServiceContract
{
    public function __construct(protected AuthorRepositoryContract $authors) {}

    /**
     * {@inheritdoc}

     *
     * @return User
     */
    public function getAuthorForProfile(int $authorId): User
    {
        return $this->authors->findById($authorId);
    }

    /**
     * Возвращает published posts.
     *
     * @param  int  $perPage  page
     */ /**
     * Возвращает опубликованные посты автора.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function getPublishedPosts(User $author, int $perPage = 10): LengthAwarePaginator
    {
        return $this->authors->getPublishedPosts($author, $perPage);
    }
}
