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
    public function upsert(CommentReactionData $data): CommentReaction;

    public function remove(int $commentId, int $userId): bool;

    /**
     * @return Collection<string, int>
     */
    public function countsForComment(int $commentId): Collection;

    /**
     * @param  list<int>  $commentIds
     * @return array<int, array<string, int>>
     */
    public function aggregateCountsForComments(array $commentIds): array;

    /**
     * @param  list<int>  $commentIds
     * @return array<int, string>
     */
    public function userReactionsForComments(array $commentIds, int $userId): array;

    public function summaryForComments(array $commentIds, ?int $userId): Collection;

    public function summaryForComment(int $commentId, ?int $userId): CommentReactionSummary;
}
