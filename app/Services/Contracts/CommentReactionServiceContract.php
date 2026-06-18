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
    public function toggle(CommentReactionData $data): ?CommentReaction;

    /**
     * @return Collection<int, CommentReactionSummary>
     */
    public function summariesForComments(array $commentIds, ?int $userId): Collection;

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
}
