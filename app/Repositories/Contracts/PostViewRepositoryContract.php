<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

/**
 * Контракт репозитория post view.
 */
interface PostViewRepositoryContract
{
    /**
     * increment.
     *
     * @param  int  $postId  id

     * @return int
     */
    public function increment(int $postId): int;

    /**
     * Возвращает count.
     *
     * @param  int  $postId  id

     * @return int
     */
    public function getCount(int $postId): int;

    /**
     * Счётчики просмотров для списка постов (БД + Redis).
     *
     * @param  list<int>  $postIds
     * @return Collection<int, int>
     */
    public function getCountsForPosts(array $postIds): Collection;

    /**
     * flush pending counts.
     */
    /**
     * flush pending counts.
     */
    /**
     * Сбрасывает отложенные счётчики просмотров.
     *
     * @return Collection<int, int>
     */
    public function flushPendingCounts(): Collection;
}
