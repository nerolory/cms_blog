<?php

namespace App\Services\Contracts;

use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Контракт сервиса author.
 */
interface AuthorServiceContract
{
    /**
     * Загружает профиль автора по id.

     *
     * @return User
     */
    public function getAuthorForProfile(int $authorId): User;

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
    public function getPublishedPosts(User $author, int $perPage = 10): LengthAwarePaginator;
}
