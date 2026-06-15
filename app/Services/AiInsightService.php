<?php

namespace App\Services;

use App\DTO\AiInsightItem;
use App\DTO\PostAiInsights;
use App\Models\AiAnalysisOrder;
use App\Models\AiToolResult;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\AiAnalysisOrderRepositoryContract;
use App\Repositories\Contracts\AiSettingsRepositoryContract;
use App\Repositories\Contracts\AiToolResultRepositoryContract;
use App\Repositories\Contracts\CommentRepositoryContract;
use App\Services\Contracts\AiInsightServiceContract;
use App\Support\TypeCast;
use Illuminate\Support\Collection;

/**
 * Сервис ai insight.

 *
 * @property-read AiToolResultRepositoryContract $results
 * @property-read AiAnalysisOrderRepositoryContract $orders
 * @property-read CommentRepositoryContract $comments
 * @property-read AiSettingsRepositoryContract $settings
 */
class AiInsightService implements AiInsightServiceContract
{
    public function __construct(protected AiToolResultRepositoryContract $results,
        protected AiAnalysisOrderRepositoryContract $orders, protected CommentRepositoryContract $comments,
        protected AiSettingsRepositoryContract $settings) {}

    /**
     * for post.
     *
     * @param  Post  $post  пост

     * @return PostAiInsights
     */
    public function forPost(Post $post, ?User $viewer = null): PostAiInsights
    {
        $commentCount = $this->comments->countVisibleForPost($post->id);
        $cached = $this->results->getCompletedForPost($post);
        /** @var Collection<int, AiInsightItem> $items */
        $items = $cached->map(function (AiToolResult $result): AiInsightItem {
            /** @var array<string, mixed> $payload */
            $payload = TypeCast::array($result->payload);

            return new AiInsightItem(toolCode: $result->tool_code, label: $result->tool_code->label(),
                summary: TypeCast::string($payload['summary'] ?? '', ''), completedAt: $result->completed_at);
        });
        $orderState = $this->orders->findInsightOrderState($post->id, $viewer?->id);
        $cooldownOrder = $orderState->cooldown;
        $cooldownUntil = $cooldownOrder?->cooldown_until;
        $hasPendingOrder = $orderState->pending instanceof AiAnalysisOrder;
        $canRequestOrder = $viewer instanceof User && $viewer
            ->can('ai.orders.request') && ! $hasPendingOrder && ($cooldownUntil === null || $cooldownUntil->isPast());
        $tokensRequired = $canRequestOrder || $hasPendingOrder
            ? $this->settings->getSettings()->tokensRequiredForComments($commentCount)
            : null;

        return new PostAiInsights(items: $items, commentCount: $commentCount, canRequestOrder: $canRequestOrder,
            hasPendingOrder: $hasPendingOrder, cooldownUntil: $cooldownUntil, tokensRequired: $tokensRequired);
    }
}
