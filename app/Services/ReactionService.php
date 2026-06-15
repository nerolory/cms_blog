<?php

namespace App\Services;

use App\DTO\ReactionData;
use App\Enums\ReactionType;
use App\Models\PostReaction;
use App\Repositories\Contracts\ReactionRepositoryContract;
use App\Services\Contracts\ReactionServiceContract;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Сервис reaction.
 *
 * @property-read ReactionRepositoryContract $reactions
 */
class ReactionService implements ReactionServiceContract
{
    public function __construct(protected ReactionRepositoryContract $reactions) {}

    /**
     * toggle.
     *
     * @param  ReactionData  $data  данные формы

     * @return ?PostReaction
     */
    public function toggle(ReactionData $data): ?PostReaction
    {
        if (ReactionType::tryFrom($data->type) === null) {
            throw new InvalidArgumentException(__('engagement.reaction.errors.invalid_type'));
        }
        $existing = $this->reactions->userReaction($data->postId, $data->userId);
        if ($existing === $data->type) {
            $this->reactions->remove($data->postId, $data->userId);

            return null;
        }

        return $this->reactions->upsert($data);
    }

    /**
     * counts for post.
     *
     * @param  int  $postId  id
     */ /**
     * Возвращает счётчики реакций для поста.
     *
     * @return Collection<int, int>
     */
    public function countsForPost(int $postId): Collection
    {
        return $this->reactions->countsForPost($postId);
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
        return $this->reactions->userReaction($postId, $userId);
    }
}
