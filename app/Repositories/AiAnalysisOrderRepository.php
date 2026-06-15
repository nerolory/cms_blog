<?php

namespace App\Repositories;

use App\DTO\AiInsightOrderState;
use App\Enums\AiAnalysisOrderStatus;
use App\Models\AiAnalysisOrder;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\AiAnalysisOrderRepositoryContract;
use Carbon\CarbonInterface;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

/**
 * Репозиторий ai analysis order.
 *
 * @property-read AiAnalysisOrder $order
 */
class AiAnalysisOrderRepository implements AiAnalysisOrderRepositoryContract
{
    public function __construct(protected AiAnalysisOrder $order) {}

    /**
     * Создаёт .
     *
     * @param  User  $user  пользователь
     * @param  Post  $post  пост
     * @param  int  $commentCount  count
     * @param  int  $tokensRequired  required

     * @return AiAnalysisOrder
     */
    public function create(User $user, Post $post, int $commentCount, int $tokensRequired): AiAnalysisOrder
    {
        return $this->order->newQuery()->create(['user_id' => $user->id, 'post_id' => $post->id,
            'comment_count' => $commentCount, 'tokens_required' => $tokensRequired,
            'status' => AiAnalysisOrderStatus::Pending]);
    }

    /**
     * Находит pending for user and post.
     *
     * @param  User  $user  пользователь
     * @param  Post  $post  пост

     * @return ?AiAnalysisOrder
     */
    public function findPendingForUserAndPost(User $user, Post $post): ?AiAnalysisOrder
    {
        return $this->order->newQuery()->where('user_id', $user->id)->where('post_id', $post->id)->where('status',
            AiAnalysisOrderStatus::Pending)->first();
    }

    /**
     * Находит active cooldown for post.
     *
     * @param  Post  $post  пост

     * @return ?AiAnalysisOrder
     */
    public function findActiveCooldownForPost(Post $post): ?AiAnalysisOrder
    {
        return $this->order->newQuery()->where('post_id',
            $post->id)->whereNotNull('cooldown_until')->where('cooldown_until', '>',
                now())->orderByDesc('cooldown_until')->first();
    }

    /**
     * Обновляет status.
     *
     * @param  ?string  $adminNote  note

     * @return AiAnalysisOrder
     */
    public function updateStatus(AiAnalysisOrder $order, AiAnalysisOrderStatus $status, ?User $reviewer = null,
        ?string $adminNote = null): AiAnalysisOrder
    {
        $order->update(['status' => $status, 'reviewed_by' => $reviewer?->id, 'admin_note' => $adminNote,
            'reviewed_at' => now()]);

        return $order->fresh() ?? $order;
    }

    /**
     * mark executed.
     *
     * @param  int  $tokensCharged  charged
     * @param  CarbonInterface  $cooldownUntil  until

     * @return AiAnalysisOrder
     */
    public function markExecuted(AiAnalysisOrder $order, int $tokensCharged,
        CarbonInterface $cooldownUntil): AiAnalysisOrder
    {
        $order->update(['status' => AiAnalysisOrderStatus::Completed, 'tokens_charged' => $tokensCharged,
            'executed_at' => now(), 'cooldown_until' => $cooldownUntil]);

        return $order->fresh() ?? $order;
    }

    /**
     * Находит by id.

     *
     * @return ?AiAnalysisOrder
     */
    public function findById(int $id): ?AiAnalysisOrder
    {
        return $this->order->newQuery()->with(['user', 'post', 'reviewer'])->find($id);
    }

    /**
     * {@inheritdoc}

     *
     * @return AiInsightOrderState
     */
    public function findInsightOrderState(int $postId, ?int $userId): AiInsightOrderState
    {
        $orders = $this->order->newQuery()->where('post_id', $postId)->where(function ($query) use ($userId): void {
            $query->where(function ($cooldown): void {
                $cooldown->whereNotNull('cooldown_until')->where('cooldown_until', '>', now());
            });
            if ($userId !== null) {
                $query->orWhere(function ($pending) use ($userId): void {
                    $pending->where('user_id', $userId)->where('status', AiAnalysisOrderStatus::Pending);
                });
            }
        })->orderByDesc('cooldown_until')->get();
        $cooldown = $orders->first(fn (AiAnalysisOrder $order): bool => $order->cooldown_until !== null && $order
            ->cooldown_until->isFuture());
        $pending = $userId !== null ? $orders->first(fn (AiAnalysisOrder $order): bool => $order
            ->user_id === $userId && $order->status === AiAnalysisOrderStatus::Pending) : null;

        return new AiInsightOrderState(cooldown: $cooldown, pending: $pending);
    }

    /**
     * run in transaction.
     */
    /**
     * Выполняет callback в транзакции БД.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function runInTransaction(callable $callback): mixed
    {
        return DB::connection()->transaction(static function (Connection $connection) use ($callback): mixed {
            unset($connection);

            return $callback();
        });
    }
}
