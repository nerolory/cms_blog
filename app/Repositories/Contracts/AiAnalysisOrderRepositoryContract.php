<?php

namespace App\Repositories\Contracts;

use App\DTO\AiInsightOrderState;
use App\Enums\AiAnalysisOrderStatus;
use App\Models\AiAnalysisOrder;
use App\Models\Post;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Контракт репозитория ai analysis order.
 */
interface AiAnalysisOrderRepositoryContract
{
    /**
     * Создаёт .
     *
     * @param  User  $user  пользователь
     * @param  Post  $post  пост
     * @param  int  $commentCount  count
     * @param  int  $tokensRequired  required

     * @return AiAnalysisOrder
     */
    public function create(User $user, Post $post, int $commentCount, int $tokensRequired): AiAnalysisOrder;

    /**
     * Находит pending for user and post.
     *
     * @param  User  $user  пользователь
     * @param  Post  $post  пост

     * @return ?AiAnalysisOrder
     */
    public function findPendingForUserAndPost(User $user, Post $post): ?AiAnalysisOrder;

    /**
     * Находит active cooldown for post.
     *
     * @param  Post  $post  пост

     * @return ?AiAnalysisOrder
     */
    public function findActiveCooldownForPost(Post $post): ?AiAnalysisOrder;

    /**
     * Обновляет status.
     *
     * @param  ?string  $adminNote  note

     * @return AiAnalysisOrder
     */
    public function updateStatus(AiAnalysisOrder $order, AiAnalysisOrderStatus $status, ?User $reviewer = null,
        ?string $adminNote = null): AiAnalysisOrder;

    /**
     * mark executed.
     *
     * @param  int  $tokensCharged  charged
     * @param  CarbonInterface  $cooldownUntil  until

     * @return AiAnalysisOrder
     */
    public function markExecuted(AiAnalysisOrder $order, int $tokensCharged,
        CarbonInterface $cooldownUntil): AiAnalysisOrder;

    /**
     * Находит by id.

     *
     * @return ?AiAnalysisOrder
     */
    public function findById(int $id): ?AiAnalysisOrder;

    /**
     * Состояние заказов для блока AI insights: cooldown и pending одним запросом.

     *
     * @return AiInsightOrderState
     */
    public function findInsightOrderState(int $postId, ?int $userId): AiInsightOrderState;

    /**
     * Выполняет callback в транзакции БД.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function runInTransaction(callable $callback): mixed;
}
