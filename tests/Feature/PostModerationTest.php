<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use App\Services\Contracts\PostServiceContract;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс post moderation.
 */
class PostModerationTest extends TestCase
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
     * Moderator approval sets status to published.
     */
    public function test_moderator_can_approve_post(): void
    {
        $author = $this->createAuthorUser();
        $moderator = User::factory()->create(['email' => 'mod@example.com']);
        $moderator->assignRole('moderator');
        $post = Post::factory()->for($author)->pendingModeration()->create();
        $approved = app(PostServiceContract::class)->approve($post, $moderator);
        $this->assertSame(PostStatus::Published, $approved->status);
        $this->assertTrue($approved->is_published);
        $this->assertNotNull($approved->published_at);
    }

    /**
     * Moderator rejection stores the reason.
     */
    public function test_moderator_can_reject_post(): void
    {
        $author = $this->createAuthorUser();
        $moderator = User::factory()->create(['email' => 'mod2@example.com']);
        $moderator->assignRole('moderator');
        $post = Post::factory()->for($author)->pendingModeration()->create();
        $rejected = app(PostServiceContract::class)->reject($post, $moderator,
            'Недостаточно информации');
        $this->assertSame(PostStatus::Rejected, $rejected->status);
        $this->assertSame('Недостаточно информации', $rejected->rejection_reason);
    }
}
