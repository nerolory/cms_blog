<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Services\Contracts\PostEngagementVersionServiceContract;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * SSE-поток engagement-блока.
 */
class PostEngagementStreamTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_engagement_stream_returns_event_stream_headers(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->published()->create();

        $response = $this->get(route('posts.engagement.stream', $post));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');
    }

    public function test_engagement_version_bumps_on_comment_create(): void
    {
        $author = $this->createAuthorUser();
        $commenter = $this->createAuthorUser(['email' => 'version-bump@example.com']);
        $post = Post::factory()->for($author)->published()->create();
        $versions = app(PostEngagementVersionServiceContract::class);

        $this->assertSame('0', $versions->get($post->id));

        $this->actingAs($commenter)->post(route('posts.comments.store', $post), ['body' => 'Bump me'])
            ->assertRedirect();

        $this->assertNotSame('0', $versions->get($post->id));
    }
}
