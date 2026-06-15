<?php

namespace App\Services\Contracts;

use App\Models\AiAnalysisOrder;
use App\Models\Post;
use App\Models\User;

/**
 * Контракт сценариев заказа AI-анализа комментариев к посту.
 */
interface AiAnalysisOrderServiceContract
{
    /**
     * Создаёт новый заказ анализа от имени пользователя.

     *
     * @return AiAnalysisOrder
     */
    public function request(User $user, Post $post): AiAnalysisOrder;

    /**
     * Одобряет заказ модератором.

     *
     * @return AiAnalysisOrder
     */
    public function approve(AiAnalysisOrder $order, User $reviewer, ?string $adminNote = null): AiAnalysisOrder;

    /**
     * Отклоняет заказ модератором.

     *
     * @return AiAnalysisOrder
     */
    public function reject(AiAnalysisOrder $order, User $reviewer, ?string $adminNote = null): AiAnalysisOrder;

    /**
     * Выполняет одобренный заказ (списание токенов и запись
     * результата).

     *
     * @return AiAnalysisOrder
     */
    public function execute(AiAnalysisOrder $order, User $executor): AiAnalysisOrder;

    /**
     * Возвращает число токенов, необходимое для анализа
     * заданного числа комментариев.

     *
     * @return int
     */
    public function calculateTokensRequired(int $commentCount): int;

    /**
     * Проверяет, разрешён ли автоматический анализ при данном
     * числе комментариев.

     *
     * @return bool
     */
    public function canAutoAnalyze(int $commentCount): bool;
}
