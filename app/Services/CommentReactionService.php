<?php

namespace App\Services;

use App\DTO\CommentReactionData;
use App\DTO\CommentReactionSummary;
use App\Enums\ReactionType;
use App\Models\CommentReaction;
use App\Repositories\Contracts\CommentReactionRepositoryContract;
use App\Services\Contracts\CommentReactionServiceContract;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Сервис реакций на комментарии.
 *
 * @property-read CommentReactionRepositoryContract $reactions
 */
class CommentReactionService implements CommentReactionServiceContract
{
    public function __construct(protected CommentReactionRepositoryContract $reactions) {}

    /**
     * toggle.
     *
     * @param  CommentReactionData  $data
     * @return ?CommentReaction
     */
    public function toggle(CommentReactionData $data): ?CommentReaction
    {
        if (ReactionType::tryFrom($data->type) === null) {
            throw new InvalidArgumentException(__('engagement.reaction.errors.invalid_type'));
        }
        $existing = $this->reactions->summaryForComment($data->commentId, $data->userId)->userReaction;
        if ($existing === $data->type) {
            $this->reactions->remove($data->commentId, $data->userId);

            return null;
        }

        return $this->reactions->upsert($data);
    }

    /**
     * summaries for comments.
     *
     * @param  Collection<int, int>  $commentIds
     * @param  ?int  $userId
     * @return Collection<int, CommentReactionSummary>
     */
    public function summariesForComments(Collection $commentIds, ?int $userId): Collection
    {
        return $this->reactions->summaryForComments($commentIds, $userId);
    }

    /**
     * aggregate counts for comments.
     *
     * @param  Collection<int, int>  $commentIds
     * @return Collection<int, Collection<string, int>>
     */
    public function aggregateCountsForComments(Collection $commentIds): Collection
    {
        return $this->reactions->aggregateCountsForComments($commentIds);
    }

    /**
     * user reactions for comments.
     *
     * @param  Collection<int, int>  $commentIds
     * @param  int  $userId
     * @return Collection<int, string>
     */
    public function userReactionsForComments(Collection $commentIds, int $userId): Collection
    {
        return $this->reactions->userReactionsForComments($commentIds, $userId);
    }
}
