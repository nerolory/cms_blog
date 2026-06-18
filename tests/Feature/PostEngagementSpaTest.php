<?php

namespace Tests\Feature;

use App\Enums\ReactionType;
use App\Models\Post;
use App\Models\PostComment;
use App\Services\Contracts\PostEngagementVersionServiceContract;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * SPA-ответы engagement-блока без redirect.
 */
class PostEngagementSpaTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_post_reaction_returns_engagement_json_when_requested(): void
    {
        $author = $this->createAuthorUser();
        $commenter = $this->createAuthorUser(['email' => 'spa-reaction@example.com']);
        $post = Post::factory()->for($author)->published()->create();

        $response = $this->actingAs($commenter)->postJson(route('posts.reactions.store', $post), [
            'type' => ReactionType::Like->value,
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['engagement_html', 'version']);
        $response->assertJsonFragment(['version' => app(PostEngagementVersionServiceContract::class)->get($post->id)]);
    }

    public function test_post_reaction_returns_engagement_json_with_spa_header(): void
    {
        $author = $this->createAuthorUser();
        $commenter = $this->createAuthorUser(['email' => 'spa-header@example.com']);
        $post = Post::factory()->for($author)->published()->create();

        $response = $this->actingAs($commenter)->post(route('posts.reactions.store', $post), [
            'type' => ReactionType::Like->value,
        ], [
            'Accept' => 'text/html',
            'X-Engagement-Spa' => '1',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['engagement_html', 'version']);
    }

    public function test_comment_reaction_returns_engagement_json_with_spa_header(): void
    {
        $author = $this->createAuthorUser();
        $commenter = $this->createAuthorUser(['email' => 'spa-comment-reaction@example.com']);
        $post = Post::factory()->for($author)->published()->create();
        $this->actingAs($commenter)->post(route('posts.comments.store', $post), ['body' => 'React me'])
            ->assertRedirect();
        $commentId = (int) PostComment::query()->where('post_id', $post->id)->value('id');

        $response = $this->actingAs($commenter)->post(route('posts.comments.reactions.store', [$post, $commentId]), [
            'type' => ReactionType::Like->value,
        ], [
            'Accept' => 'text/html',
            'X-Engagement-Spa' => '1',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['engagement_html', 'version']);
    }

    public function test_post_reaction_without_spa_header_redirects_back(): void
    {
        $author = $this->createAuthorUser();
        $commenter = $this->createAuthorUser(['email' => 'spa-redirect@example.com']);
        $post = Post::factory()->for($author)->published()->create();

        $response = $this->actingAs($commenter)->from(route('posts.show', $post))->post(
            route('posts.reactions.store', $post),
            ['type' => ReactionType::Like->value],
        );

        $response->assertRedirect(route('posts.show', $post));
    }

    public function test_comment_store_returns_engagement_json_when_requested(): void
    {
        $author = $this->createAuthorUser();
        $commenter = $this->createAuthorUser(['email' => 'spa-comment@example.com']);
        $post = Post::factory()->for($author)->published()->create();

        $response = $this->actingAs($commenter)->postJson(route('posts.comments.store', $post), [
            'body' => 'SPA comment',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['engagement_html', 'version']);
        $this->assertStringContainsString('SPA comment', (string) $response->json('engagement_html'));
    }

    public function test_post_show_reaction_form_uses_hidden_type_field(): void
    {
        $author = $this->createAuthorUser();
        $viewer = $this->createAuthorUser(['email' => 'reaction-form@example.com']);
        $post = Post::factory()->for($author)->published()->create();

        $response = $this->actingAs($viewer)->get(route('posts.show', $post));

        $response->assertOk();
        $response->assertSee('data-engagement-reaction-type', false);
        $response->assertSee('data-reaction-type="like"', false);
        $response->assertDontSee('name="type" value="like"', false);
    }

    public function test_engagement_fragment_endpoint_returns_html(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->published()->create();

        $response = $this->getJson(route('posts.engagement.show', $post));
        $response->assertOk();
        $response->assertJsonStructure(['engagement_html', 'version']);
        $this->assertStringContainsString('data-comments-section', (string) $response->json('engagement_html'));
    }
}
