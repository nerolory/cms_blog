<?php

namespace App\Repositories\Contracts;

use App\DTO\ReactionData;
use App\DTO\ReactionEngagementData;
use App\Models\PostReaction;
use Illuminate\Support\Collection;

/**
 * Контракт репозитория reaction.
 */
interface ReactionRepositoryContract
{
    /**
     * upsert.
     *
     * @param  ReactionData  $data  данные формы

     * @return PostReaction
     */
    public function upsert(ReactionData $data): PostReaction;

    /**
     * remove.
     *
     * @param  int  $postId  id
     * @param  int  $userId  id

     * @return bool
     */
    public function remove(int $postId, int $userId): bool;

    /**
     * counts for post.
     *
     * @param  int  $postId  id
     */
    /**
     * counts for post.
     */
    /**
     * Возвращает счётчики реакций для поста.
     *
     * @return Collection<int, int>
     */
    public function countsForPost(int $postId): Collection;

    /**
     * user reaction.
     *
     * @param  int  $postId  id
     * @param  int  $userId  id

     * @return ?string
     */
    public function userReaction(int $postId, int $userId): ?string;

    /**
     * counts and user reaction for post.

     *
     * @return ReactionEngagementData
     */
    public function countsAndUserReactionForPost(int $postId, ?int $userId): ReactionEngagementData;
}
