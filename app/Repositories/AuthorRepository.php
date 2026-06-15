<?php

namespace App\Repositories;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\AuthorRepositoryContract;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Репозиторий author.
 *
 * @property-read Post $post
 * @property-read User $user
 */
class AuthorRepository implements AuthorRepositoryContract
{
    public function __construct(protected Post $post, protected User $user) {}

    /**
     * {@inheritdoc}

     *
     * @return User
     */
    public function findById(int $authorId): User
    {
        return $this->user->newQuery()->findOrFail($authorId);
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
    public function getPublishedPosts(User $author, int $perPage): LengthAwarePaginator
    {
        return $this->post->newQuery()->with(['category', 'tags'])->where('user_id', $author->id)->where('status',
            PostStatus::Published)->orderByDesc('published_at')->paginate($perPage);
    }
}
