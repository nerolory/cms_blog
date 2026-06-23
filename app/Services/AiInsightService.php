<?php

namespace App\Services;

use App\DTO\AiInsightItem;
use App\DTO\PostAiInsights;
use App\Enums\AiToolCode;
use App\Models\AiAnalysisOrder;
use App\Models\AiToolResult;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\AiAnalysisOrderRepositoryContract;
use App\Repositories\Contracts\AiToolResultRepositoryContract;
use App\Services\Contracts\AiInsightServiceContract;
use App\Services\Contracts\AiSettingsServiceContract;
use App\Support\TypeCast;
use Illuminate\Support\Collection;

/**
 * Сервис ai insight.
 *
 * @property-read AiToolResultRepositoryContract $results
 * @property-read AiAnalysisOrderRepositoryContract $orders
 * @property-read AiSettingsServiceContract $settings
 */
class AiInsightService implements AiInsightServiceContract
{
    public function __construct(
        protected AiToolResultRepositoryContract $results,
        protected AiAnalysisOrderRepositoryContract $orders,
        protected AiSettingsServiceContract $settings,
    ) {}

    /**
     * {@inheritdoc}

     *
     * @return PostAiInsights
     */
    public function forPost(Post $post, ?User $viewer = null, ?int $visibleCommentCount = null): PostAiInsights
    {
        $commentCount = $visibleCommentCount ?? 0;
        $cached = $this->results->getCompletedForPost($post);
        /** @var Collection<int, AiInsightItem> $items */
        $items = $cached->map(function (AiToolResult $result): AiInsightItem {
            /** @var array<string, mixed> $payload */
            $payload = TypeCast::array($result->payload);
            $toolCode = $result->tool_code instanceof AiToolCode
                ? $result->tool_code
                : AiToolCode::tryFrom(TypeCast::string($result->tool_code)) ?? AiToolCode::CommentSummary;

            return new AiInsightItem(
                toolCode: $toolCode,
                label: $toolCode->label(),
                summary: TypeCast::string($payload['summary'] ?? '', ''),
                completedAt: $result->completed_at,
            );
        });

        $cooldownOrder = null;
        $pending = null;
        if ($viewer instanceof User && $viewer->can('ai.orders.request')) {
            $orderState = $this->orders->findInsightOrderState($post->id, $viewer->id);
            $cooldownOrder = $orderState->cooldown;
            $pending = $orderState->pending;
        }

        $cooldownUntil = $cooldownOrder?->cooldown_until;
        $hasPendingOrder = $pending instanceof AiAnalysisOrder;
        $canRequestOrder = $viewer instanceof User
            && $viewer->can('ai.orders.request')
            && ! $hasPendingOrder
            && ($cooldownUntil === null || $cooldownUntil->isPast());
        $tokensRequired = $canRequestOrder || $hasPendingOrder
            ? $this->settings->settings()->tokensRequiredForComments($commentCount)
            : null;

        return new PostAiInsights(
            items: $items,
            commentCount: $commentCount,
            canRequestOrder: $canRequestOrder,
            hasPendingOrder: $hasPendingOrder,
            cooldownUntil: $cooldownUntil,
            tokensRequired: $tokensRequired,
        );
    }
}
