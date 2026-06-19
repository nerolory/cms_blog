<?php

namespace App\Services\Contracts;

use App\DTO\CommentReactionData;
use App\DTO\CommentReactionSummary;
use App\Models\CommentReaction;
use Illuminate\Support\Collection;

/**
 * Контракт сервиса реакций на комментарии.
 */
interface CommentReactionServiceContract
{
    /**
     * toggle.
     *
     * @param  CommentReactionData  $data
     * @return ?CommentReaction
     */
    public function toggle(CommentReactionData $data): ?CommentReaction;

    /**
     * summaries for comments.
     *
     * @param  Collection<int, int>  $commentIds
     * @param  ?int  $userId
     * @return Collection<int, CommentReactionSummary>
     */
    public function summariesForComments(Collection $commentIds, ?int $userId): Collection;

    /**
     * aggregate counts for comments.
     *
     * @param  Collection<int, int>  $commentIds
     * @return Collection<int, Collection<string, int>>
     */
    public function aggregateCountsForComments(Collection $commentIds): Collection;

    /**
     * user reactions for comments.
     *
     * @param  Collection<int, int>  $commentIds
     * @param  int  $userId
     * @return Collection<int, string>
     */
    public function userReactionsForComments(Collection $commentIds, int $userId): Collection;
}
