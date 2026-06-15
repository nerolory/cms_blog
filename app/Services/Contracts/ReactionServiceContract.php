<?php

namespace App\Services\Contracts;

use App\DTO\ReactionData;
use App\Models\PostReaction;
use Illuminate\Support\Collection;

/**
 * Контракт сервиса reaction.
 */
interface ReactionServiceContract
{
    /**
     * toggle.
     *
     * @param  ReactionData  $data  данные формы

     * @return ?PostReaction
     */
    public function toggle(ReactionData $data): ?PostReaction;

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
}
