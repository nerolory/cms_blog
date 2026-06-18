<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReactionType;
use App\Models\Post;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Проверка реакций на реальном slug через HTTP kernel (не postJson).
 */
class PostEngagementBrowserProbeTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_post_reaction_via_form_fields_returns_engagement_json(): void
    {
        $author = $this->createAuthorUser();
        $viewer = $this->createAuthorUser(['email' => 'browser-probe@example.com']);
        $post = Post::factory()->for($author)->published()->create(['slug' => 'browser-probe-post']);

        $show = $this->actingAs($viewer)->get(route('posts.show', $post));
        $show->assertOk();
        $show->assertSee('data-engagement-reaction-type', false);

        $response = $this->actingAs($viewer)->post(route('posts.reactions.store', $post), [
            '_token' => csrf_token(),
            'type' => ReactionType::Like->value,
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
            'X-Engagement-Spa' => '1',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['engagement_html', 'version']);
    }
}
