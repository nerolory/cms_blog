<?php

namespace App\Repositories\Contracts;

use App\DTO\PostVersionSnapshot;
use App\Models\Post;
use App\Models\PostVersion;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Контракт репозитория post version.
 */
interface PostVersionRepositoryContract
{
    /**
     * Создаёт snapshot.
     *
     * @param  Post  $post  пост

     * @return PostVersion
     */
    public function createSnapshot(Post $post, PostVersionSnapshot $snapshot, User $actor): PostVersion;

    /**
     * list for post.
     *
     * @param  Post  $post  пост
     */
    /**
     * list for post.
     */
    /**
     * Возвращает версии поста.
     *
     * @return Collection<int, PostVersion>
     */
    public function listForPost(Post $post): Collection;

    /**
     * Находит for post.
     *
     * @param  Post  $post  пост
     * @param  int  $versionId  id

     * @return ?PostVersion
     */
    public function findForPost(Post $post, int $versionId): ?PostVersion;

    /**
     * prune beyond limit.
     *
     * @param  Post  $post  пост
     * @param  int  $maxVersions  versions

     * @return int
     */
    public function pruneBeyondLimit(Post $post, int $maxVersions): int;

    /**
     * Удаляет all for post.
     *
     * @param  Post  $post  пост

     * @return int
     */
    public function deleteAllForPost(Post $post): int;
}
