<?php

namespace App\Services;

use App\Enums\AiAnalysisOrderStatus;
use App\Enums\AiResultStatus;
use App\Enums\AiToolCode;
use App\Exceptions\AiAnalysisOrderException;
use App\Models\AiAnalysisOrder;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\AiAnalysisOrderRepositoryContract;
use App\Repositories\Contracts\AiToolResultRepositoryContract;
use App\Repositories\Contracts\CommentRepositoryContract;
use App\Repositories\Contracts\UserTokenWalletRepositoryContract;
use App\Services\Contracts\AiAnalysisOrderServiceContract;
use App\Services\Contracts\AiSettingsServiceContract;
use App\Services\Contracts\TokenWalletServiceContract;

/**
 * Сценарии заказа AI-анализа комментариев к посту.
 *
 * Создание заказа пользователем, модерация (approve/reject), списание
 * токенов
 * и запись демо-результата в read-through кэш (`ai_tool_results`).
 *
 * @property-read AiAnalysisOrderRepositoryContract $orders
 * @property-read CommentRepositoryContract $comments
 * @property-read AiSettingsServiceContract $settings
 * @property-read AiToolResultRepositoryContract $results
 * @property-read UserTokenWalletRepositoryContract $wallets
 * @property-read TokenWalletServiceContract $tokenWallet
 */
class AiAnalysisOrderService implements AiAnalysisOrderServiceContract
{
    public function __construct(protected AiAnalysisOrderRepositoryContract $orders,
        protected CommentRepositoryContract $comments, protected AiSettingsServiceContract $settings,
        protected AiToolResultRepositoryContract $results, protected UserTokenWalletRepositoryContract $wallets,
        protected TokenWalletServiceContract $tokenWallet) {}

    /**
     * Создаёт новый заказ анализа, если нет активного cooldown и
     * pending-заказа.

     *
     * @return AiAnalysisOrder
     */
    public function request(User $user, Post $post): AiAnalysisOrder
    {
        if ($this->orders->findPendingForUserAndPost($user, $post) instanceof AiAnalysisOrder) {
            throw AiAnalysisOrderException::pendingOrderExists();
        }
        $cooldownOrder = $this->orders->findActiveCooldownForPost($post);
        if ($cooldownOrder?->cooldown_until !== null && $cooldownOrder->cooldown_until->isFuture()) {
            throw AiAnalysisOrderException::cooldownActive($cooldownOrder->cooldown_until);
        }
        $commentCount = $this->comments->countVisibleForPost($post->id);
        $tokensRequired = $this->calculateTokensRequired($commentCount);

        return $this->orders->create($user, $post, $commentCount, $tokensRequired);
    }

    /**
     * Одобряет заказ модератором (переход pending → approved).

     *
     * @return AiAnalysisOrder
     */
    public function approve(AiAnalysisOrder $order, User $reviewer, ?string $adminNote = null): AiAnalysisOrder
    {
        $this->assertStatus($order, AiAnalysisOrderStatus::Pending);

        return $this->orders->updateStatus($order, AiAnalysisOrderStatus::Approved, $reviewer, $adminNote);
    }

    /**
     * Отклоняет заказ модератором (переход pending → rejected).

     *
     * @return AiAnalysisOrder
     */
    public function reject(AiAnalysisOrder $order, User $reviewer, ?string $adminNote = null): AiAnalysisOrder
    {
        $this->assertStatus($order, AiAnalysisOrderStatus::Pending);

        return $this->orders->updateStatus($order, AiAnalysisOrderStatus::Rejected, $reviewer, $adminNote);
    }

    /**
     * Выполняет одобренный заказ: списание токенов,
     * демо-результат, cooldown.

     *
     * @return AiAnalysisOrder
     */
    public function execute(AiAnalysisOrder $order, User $executor): AiAnalysisOrder
    {
        $this->assertStatus($order, AiAnalysisOrderStatus::Approved);
        $orderUser = $this->resolveOrderUser($order);
        $post = $this->resolveOrderPost($order);
        $this->tokenWallet->ensureSufficientBalance($orderUser, $order->tokens_required);

        return $this->orders->runInTransaction(
            fn (): AiAnalysisOrder => $this->finalizeExecution($order, $orderUser, $post),
        );
    }

    /**
     * Рассчитывает стоимость анализа по числу комментариев и
     * настройкам тарифа.

     *
     * @return int
     */
    public function calculateTokensRequired(int $commentCount): int
    {
        return $this->settings->settings()->tokensRequiredForComments($commentCount);
    }

    /**
     * Проверяет, достигнут ли порог комментариев для
     * автоматического анализа.

     *
     * @return bool
     */
    public function canAutoAnalyze(int $commentCount): bool
    {
        $settings = $this->settings->settings();

        return $commentCount >= $settings->autoAnalysisCommentThreshold;
    }

    private function assertStatus(AiAnalysisOrder $order, AiAnalysisOrderStatus $expected): void
    {
        if ($order->status !== $expected) {
            throw AiAnalysisOrderException::invalidStatusTransition();
        }
    }

    private function resolveOrderUser(AiAnalysisOrder $order): User
    {
        $orderUser = $order->user;
        if (! $orderUser instanceof User) {
            throw AiAnalysisOrderException::invalidStatusTransition();
        }

        return $orderUser;
    }

    private function resolveOrderPost(AiAnalysisOrder $order): Post
    {
        $post = $order->post;
        if (! $post instanceof Post) {
            throw AiAnalysisOrderException::invalidStatusTransition();
        }

        return $post;
    }

    private function finalizeExecution(AiAnalysisOrder $order, User $orderUser, Post $post): AiAnalysisOrder
    {
        $this->orders->updateStatus($order, AiAnalysisOrderStatus::Executing);
        $this->wallets->debit($orderUser, $order->tokens_required, AiAnalysisOrder::class, $order->id,
            __('ai.orders.execution_note'));
        $summary = __('ai.orders.demo_summary', ['count' => $order->comment_count, 'title' => $post->title]);
        $this->results->upsertForPost($post, AiToolCode::CommentSummary, AiResultStatus::Completed, $summary,
            __('ai.orders.demo_detail'));
        $settings = $this->settings->settings();

        return $this->orders->markExecuted($order, $order->tokens_required, now()->addDays($settings->cooldownDays));
    }
}
