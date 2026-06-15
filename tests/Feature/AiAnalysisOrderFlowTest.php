<?php

namespace Tests\Feature;

use App\DTO\TokenGrantData;
use App\Enums\AiAnalysisOrderStatus;
use App\Enums\AiResultStatus;
use App\Enums\AiToolCode;
use App\Enums\CommentStatus;
use App\Models\AiAnalysisOrder;
use App\Models\AiToolResult;
use App\Models\Post;
use App\Models\PostComment;
use App\Services\Contracts\AiAnalysisOrderServiceContract;
use App\Services\Contracts\AiInsightServiceContract;
use App\Services\Contracts\TokenWalletServiceContract;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс ai analysis order flow.
 */
class AiAnalysisOrderFlowTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    /**
     * Подготавливает окружение теста.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    /**
     * test user can request analysis order.
     */
    public function test_user_can_request_analysis_order(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->published()->create();
        PostComment::query()->create(['post_id' => $post->id, 'user_id' => $author->id,
            'body' => 'Первый комментарий для анализа', 'status' => CommentStatus::Visible]);
        $response = $this->actingAs($author)->post(route('posts.ai-analysis.store', $post));
        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('ai_analysis_orders', ['user_id' => $author->id, 'post_id' => $post->id,
            'status' => AiAnalysisOrderStatus::Pending->value, 'comment_count' => 1, 'tokens_required' => 10]);
    }

    /**
     * test cooldown blocks second order.
     */
    public function test_cooldown_blocks_second_order(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->published()->create();
        AiAnalysisOrder::query()->create(['user_id' => $author->id, 'post_id' => $post->id, 'comment_count' => 5,
            'tokens_required' => 10, 'status' => AiAnalysisOrderStatus::Completed,
            'cooldown_until' => now()->addDays(3)]);
        $this->actingAs($author)->from(route('posts.show', $post))->post(route('posts.ai-analysis.store',
            $post))->assertRedirect(route('posts.show', $post))->assertSessionHasErrors('ai_order');
    }

    /**
     * test admin flow approve execute writes cache and charges tokens.
     */
    public function test_admin_flow_approve_execute_writes_cache_and_charges_tokens(): void
    {
        $author = $this->createAuthorUser();
        $moderator = $this->createModeratorUser();
        $post = Post::factory()->for($author)->published()->create(['title' => 'Пост для AI теста']);
        app(TokenWalletServiceContract::class)->grant(new TokenGrantData(userId: $author->id, amount: 50,
            grantedByUserId: $moderator->id));
        $order = app(AiAnalysisOrderServiceContract::class)->request($author, $post);
        $order = app(AiAnalysisOrderServiceContract::class)->approve($order, $moderator, 'ok');
        $order = app(AiAnalysisOrderServiceContract::class)->execute($order, $moderator);
        $this->assertSame(AiAnalysisOrderStatus::Completed, $order->status);
        $this->assertSame(10, $order->tokens_charged);
        $this->assertSame(40, app(TokenWalletServiceContract::class)->getBalance($author));
        $this->assertDatabaseHas('ai_tool_results', ['subject_type' => Post::class, 'subject_id' => $post->id,
            'tool_code' => AiToolCode::CommentSummary->value, 'status' => AiResultStatus::Completed->value]);
    }

    /**
     * test post show displays cached ai insights.
     */
    public function test_post_show_displays_cached_ai_insights(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->published()->create(['title' => 'Кэшированный AI пост']);
        AiToolResult::query()->create(['subject_type' => Post::class, 'subject_id' => $post->id,
            'tool_code' => AiToolCode::CommentSummary, 'status' => AiResultStatus::Completed,
            'payload' => ['summary' => 'Кэшированная сводка комментариев'],
            'completed_at' => now()]);
        $this->get(route('posts.show',
            $post))->assertOk()->assertSee('Кэшированная сводка комментариев',
                false)->assertSee(__('ai.insights.title'), false);
    }

    /**
     * test insight service reads only completed cache.
     */
    public function test_insight_service_reads_only_completed_cache(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->published()->create();
        AiToolResult::query()->create(['subject_type' => Post::class, 'subject_id' => $post->id,
            'tool_code' => AiToolCode::CommentSummary, 'status' => AiResultStatus::Pending,
            'payload' => ['summary' => 'Не должно отображаться']]);
        $insights = app(AiInsightServiceContract::class)->forPost($post, $author);
        $this->assertFalse($insights->hasCache());
        $this->assertCount(0, $insights->items);
    }
}
