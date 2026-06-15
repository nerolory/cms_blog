<?php

namespace App\Services\Contracts;

use App\Models\Post;
use App\Models\PostVersion;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Контракт сервиса post version.
 */
interface PostVersionServiceContract
{
    /**
     * Saves current post state as a version and prunes to max 2.

     *
     * @return PostVersion
     */
    public function snapshotBeforeUpdate(Post $post, User $actor): PostVersion;

    /**
     * Возвращает историю версий поста.
     *
     * @return Collection<int, PostVersion>
     */
    public function historyFor(Post $post): Collection;

    /**
     * restore.

     *
     * @return Post
     */
    public function restore(PostVersion $version, User $actor): Post;
}
