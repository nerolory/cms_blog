<?php

namespace App\Repositories;

use App\DTO\CommentReactionData;
use App\DTO\CommentReactionSummary;
use App\Enums\ReactionType;
use App\Models\CommentReaction;
use App\Repositories\Contracts\CommentReactionRepositoryContract;
use App\Support\TypeCast;
use Illuminate\Support\Collection;

/**
 * Репозиторий реакций на комментарии.
 */
class CommentReactionRepository implements CommentReactionRepositoryContract
{
    public function __construct(protected CommentReaction $reaction) {}

    public function upsert(CommentReactionData $data): CommentReaction
    {
        return $this->reaction->newQuery()->updateOrCreate(
            ['comment_id' => $data->commentId, 'user_id' => $data->userId],
            ['type' => $data->type],
        );
    }

    public function remove(int $commentId, int $userId): bool
    {
        return $this->reaction->newQuery()->where('comment_id', $commentId)->where('user_id', $userId)->delete() > 0;
    }

    public function countsForComment(int $commentId): Collection
    {
        $rows = $this->reaction->newQuery()->selectRaw('type, COUNT(*) as aggregate')->where('comment_id',
            $commentId)->groupBy('type')->pluck('aggregate', 'type');

        return $this->normalizeCounts($rows);
    }

    public function summaryForComments(array $commentIds, ?int $userId): Collection
    {
        if ($commentIds === []) {
            return collect();
        }

        $aggregates = $this->loadAggregatesForComments($commentIds);

        return $this->summariesFromAggregates($commentIds, $aggregates,
            $userId !== null ? $this->userReactionsForComments($commentIds, $userId) : []);
    }

    /**
     * {@inheritdoc}
     */
    public function aggregateCountsForComments(array $commentIds): array
    {
        if ($commentIds === []) {
            return [];
        }

        return $this->loadAggregatesForComments($commentIds);
    }

    /**
     * {@inheritdoc}
     */
    public function userReactionsForComments(array $commentIds, int $userId): array
    {
        if ($commentIds === []) {
            return [];
        }

        $rows = $this->reaction->newQuery()
            ->select(['comment_id', 'type'])
            ->whereIn('comment_id', $commentIds)
            ->where('user_id', $userId)
            ->get();
        $userReactions = [];
        foreach ($rows as $row) {
            $commentId = TypeCast::int($row->comment_id);
            $userReactions[$commentId] = $row->type instanceof ReactionType
                ? $row->type->value
                : TypeCast::string($row->type);
        }

        return $userReactions;
    }

    /**
     * @param  list<int>  $commentIds
     * @return array<int, array<string, int>>
     */
    private function loadAggregatesForComments(array $commentIds): array
    {
        $rows = $this->reaction->newQuery()->select(['comment_id', 'user_id', 'type'])->whereIn('comment_id',
            $commentIds)->get();
        $aggregates = [];
        foreach ($rows as $row) {
            $commentId = TypeCast::int($row->comment_id);
            $type = $row->type instanceof ReactionType ? $row->type->value : TypeCast::string($row->type);
            $aggregates[$commentId][$type] = ($aggregates[$commentId][$type] ?? 0) + 1;
        }

        return $aggregates;
    }

    /**
     * @param  list<int>  $commentIds
     * @param  array<int, array<string, int>>  $aggregates
     * @param  array<int, string>  $userReactions
     * @return Collection<int, CommentReactionSummary>
     */
    private function summariesFromAggregates(array $commentIds, array $aggregates, array $userReactions): Collection
    {
        $summaries = collect();
        foreach ($commentIds as $commentId) {
            $counts = $this->normalizeCounts(collect($aggregates[$commentId] ?? []));
            $summaries->put($commentId, new CommentReactionSummary(
                counts: $counts,
                userReaction: $userReactions[$commentId] ?? null,
            ));
        }

        return $summaries;
    }

    public function summaryForComment(int $commentId, ?int $userId): CommentReactionSummary
    {
        return $this->summaryForComments([$commentId], $userId)->get($commentId)
            ?? new CommentReactionSummary(counts: $this->normalizeCounts(collect()));
    }

    /**
     * @param  Collection<int|string, mixed>  $rows
     * @return Collection<string, int>
     */
    private function normalizeCounts(Collection $rows): Collection
    {
        $counts = collect();
        foreach (ReactionType::all() as $type) {
            $counts->put($type->value, TypeCast::int($rows[$type->value] ?? 0));
        }

        return $counts;
    }
}
