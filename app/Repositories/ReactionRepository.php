<?php

namespace App\Repositories;

use App\DTO\ReactionData;
use App\DTO\ReactionEngagementData;
use App\Enums\ReactionType;
use App\Models\PostReaction;
use App\Repositories\Contracts\ReactionRepositoryContract;
use App\Support\TypeCast;
use Illuminate\Support\Collection;

/**
 * Репозиторий reaction.
 *
 * @property-read PostReaction $reaction
 */
class ReactionRepository implements ReactionRepositoryContract
{
    public function __construct(protected PostReaction $reaction) {}

    /**
     * upsert.
     *
     * @param  ReactionData  $data  данные формы

     * @return PostReaction
     */
    public function upsert(ReactionData $data): PostReaction
    {
        return $this->reaction->newQuery()->updateOrCreate(['post_id' => $data->postId, 'user_id' => $data->userId],
            ['type' => $data->type]);
    }

    /**
     * remove.
     *
     * @param  int  $postId  id
     * @param  int  $userId  id

     * @return bool
     */
    public function remove(int $postId, int $userId): bool
    {
        return $this->reaction->newQuery()->where('post_id', $postId)->where('user_id', $userId)->delete() > 0;
    }

    /**
     * counts for post.
     *
     * @param  int  $postId  id
     */
    /**
     * Возвращает счётчики реакций для поста.
     *
     * @return Collection<int, int>
     */
    public function countsForPost(int $postId): Collection
    {
        $rows = $this->reaction->newQuery()->selectRaw('type, COUNT(*) as aggregate')->where('post_id',
            $postId)->groupBy('type')->pluck('aggregate', 'type');
        $counts = collect();
        foreach (ReactionType::all() as $type) {
            $counts->put($type->value, TypeCast::int($rows[$type->value] ?? 0));
        }

        return $counts;
    }

    /**
     * user reaction.
     *
     * @param  int  $postId  id
     * @param  int  $userId  id

     * @return ?string
     */
    public function userReaction(int $postId, int $userId): ?string
    {
        $reaction = $this->reaction->newQuery()->where('post_id', $postId)->where('user_id', $userId)->first();
        if ($reaction === null) {
            return null;
        }

        return $reaction->type instanceof ReactionType ? $reaction->type->value : (is_string($reaction
            ->type) ? $reaction->type : null);
    }

    /**
     * {@inheritdoc}

     *
     * @return ReactionEngagementData
     */
    public function countsAndUserReactionForPost(int $postId, ?int $userId): ReactionEngagementData
    {
        $rows = $this->reaction->newQuery()->select(['user_id', 'type'])->where('post_id', $postId)->get();
        $aggregates = [];
        $userReaction = null;
        foreach ($rows as $row) {
            $type = $row->type instanceof ReactionType ? $row->type->value : (string) $row->type;
            $aggregates[$type] = ($aggregates[$type] ?? 0) + 1;
            if ($userId !== null && (int) $row->user_id === $userId) {
                $userReaction = $type;
            }
        }
        $counts = collect();
        foreach (ReactionType::all() as $type) {
            $counts->put($type->value, TypeCast::int($aggregates[$type->value] ?? 0));
        }

        return new ReactionEngagementData(counts: $counts, userReaction: $userReaction);
    }
}
