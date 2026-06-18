<?php

namespace App\Services;

use App\DTO\CommentReactionData;
use App\Enums\ReactionType;
use App\Models\CommentReaction;
use App\Repositories\Contracts\CommentReactionRepositoryContract;
use App\Services\Contracts\CommentReactionServiceContract;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Сервис реакций на комментарии.
 */
class CommentReactionService implements CommentReactionServiceContract
{
    public function __construct(protected CommentReactionRepositoryContract $reactions) {}

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

    public function summariesForComments(array $commentIds, ?int $userId): Collection
    {
        return $this->reactions->summaryForComments($commentIds, $userId);
    }

    public function aggregateCountsForComments(array $commentIds): array
    {
        return $this->reactions->aggregateCountsForComments($commentIds);
    }

    public function userReactionsForComments(array $commentIds, int $userId): array
    {
        return $this->reactions->userReactionsForComments($commentIds, $userId);
    }
}
