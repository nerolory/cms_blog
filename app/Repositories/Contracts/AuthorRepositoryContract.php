<?php

namespace App\Repositories\Contracts;

use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Контракт репозитория author.
 */
interface AuthorRepositoryContract
{
    /**
     * Находит автора по id.

     *
     * @return User
     */
    public function findById(int $authorId): User;

    /**
     * Возвращает published posts.
     *
     * @param  int  $perPage  page
     */
    /**
     * Возвращает published posts.
     */
    /**
     * Возвращает опубликованные посты автора.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function getPublishedPosts(User $author, int $perPage): LengthAwarePaginator;
}
