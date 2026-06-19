<?php

namespace App\Repositories\Contracts;

use App\DTO\CommentReactionData;
use App\DTO\CommentReactionSummary;
use App\Models\CommentReaction;
use Illuminate\Support\Collection;

/**
 * Контракт репозитория реакций на комментарии.
 */
interface CommentReactionRepositoryContract
{
    /**
     * upsert.
     *
     * @param  CommentReactionData  $data
     * @return CommentReaction
     */
    public function upsert(CommentReactionData $data): CommentReaction;

    /**
     * remove.
     *
     * @param  int  $commentId
     * @param  int  $userId
     * @return bool
     */
    public function remove(int $commentId, int $userId): bool;

    /**
     * counts for comment.
     *
     * @param  int  $commentId
     * @return Collection<string, int>
     */
    public function countsForComment(int $commentId): Collection;

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

    /**
     * summary for comments.
     *
     * @param  Collection<int, int>  $commentIds
     * @param  ?int  $userId
     * @return Collection<int, CommentReactionSummary>
     */
    public function summaryForComments(Collection $commentIds, ?int $userId): Collection;

    /**
     * summary for comment.
     *
     * @param  int  $commentId
     * @param  ?int  $userId
     * @return CommentReactionSummary
     */
    public function summaryForComment(int $commentId, ?int $userId): CommentReactionSummary;
}
