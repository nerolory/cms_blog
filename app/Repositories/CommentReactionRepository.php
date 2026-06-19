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
 *
 * @property-read CommentReaction $reaction
 */
class CommentReactionRepository implements CommentReactionRepositoryContract
{
    public function __construct(protected CommentReaction $reaction) {}

    /**
     * upsert.
     *
     * @param  CommentReactionData  $data
     * @return CommentReaction
     */
    public function upsert(CommentReactionData $data): CommentReaction
    {
        return $this->reaction->newQuery()->updateOrCreate(
            ['comment_id' => $data->commentId, 'user_id' => $data->userId],
            ['type' => $data->type],
        );
    }

    /**
     * remove.
     *
     * @param  int  $commentId
     * @param  int  $userId
     * @return bool
     */
    public function remove(int $commentId, int $userId): bool
    {
        return $this->reaction->newQuery()->where('comment_id', $commentId)->where('user_id', $userId)->delete() > 0;
    }

    /**
     * counts for comment.
     *
     * @param  int  $commentId
     * @return Collection<string, int>
     */
    public function countsForComment(int $commentId): Collection
    {
        $rows = $this->reaction->newQuery()->selectRaw('type, COUNT(*) as aggregate')->where('comment_id',
            $commentId)->groupBy('type')->pluck('aggregate', 'type')
            ->map(fn (mixed $count): int => TypeCast::int($count));

        return $this->normalizeCounts($rows->all());
    }

    /**
     * summary for comments.
     *
     * @param  Collection<int, int>  $commentIds
     * @param  ?int  $userId
     * @return Collection<int, CommentReactionSummary>
     */
    public function summaryForComments(Collection $commentIds, ?int $userId): Collection
    {
        if ($commentIds->isEmpty()) {
            return collect();
        }

        $ids = array_values($commentIds->all());
        $aggregates = $this->loadAggregatesForComments($ids);

        return $this->summariesFromAggregates(
            $ids,
            $aggregates,
            $userId !== null ? $this->userReactionsForComments($commentIds, $userId) : collect(),
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return Collection<int, Collection<string, int>>
     */
    public function aggregateCountsForComments(Collection $commentIds): Collection
    {
        if ($commentIds->isEmpty()) {
            return collect();
        }

        $ids = array_values($commentIds->all());
        $aggregates = $this->loadAggregatesForComments($ids);
        $result = collect();
        foreach ($ids as $commentId) {
            $result->put($commentId, collect($aggregates[$commentId] ?? []));
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @return Collection<int, string>
     */
    public function userReactionsForComments(Collection $commentIds, int $userId): Collection
    {
        if ($commentIds->isEmpty()) {
            return collect();
        }

        $ids = array_values($commentIds->all());
        $rows = $this->reaction->newQuery()
            ->select(['comment_id', 'type'])
            ->whereIn('comment_id', $ids)
            ->where('user_id', $userId)
            ->get();
        $userReactions = collect();
        foreach ($rows as $row) {
            $commentId = TypeCast::int($row->comment_id);
            $userReactions->put($commentId, $row->type instanceof ReactionType
                ? $row->type->value
                : TypeCast::string($row->type));
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
     * @param  Collection<int, string>  $userReactions
     * @return Collection<int, CommentReactionSummary>
     */
    private function summariesFromAggregates(
        array $commentIds,
        array $aggregates,
        Collection $userReactions,
    ): Collection {
        $summaries = collect();
        foreach ($commentIds as $commentId) {
            $counts = $this->normalizeCounts($aggregates[$commentId] ?? []);
            $summaries->put($commentId, new CommentReactionSummary(
                counts: $counts,
                userReaction: $userReactions->get($commentId),
            ));
        }

        return $summaries;
    }

    /**
     * summary for comment.
     *
     * @param  int  $commentId
     * @param  ?int  $userId
     * @return CommentReactionSummary
     */
    public function summaryForComment(int $commentId, ?int $userId): CommentReactionSummary
    {
        $summary = $this->summaryForComments(collect([$commentId]), $userId)->get($commentId);

        return $summary instanceof CommentReactionSummary
            ? $summary
            : new CommentReactionSummary(counts: $this->normalizeCounts([]));
    }

    /**
     * @param  array<int|string, mixed>  $rows
     * @return Collection<string, int>
     */
    private function normalizeCounts(array $rows): Collection
    {
        $counts = collect();
        foreach (ReactionType::all() as $type) {
            $counts->put($type->value, TypeCast::int($rows[$type->value] ?? 0));
        }

        return $counts;
    }
}
