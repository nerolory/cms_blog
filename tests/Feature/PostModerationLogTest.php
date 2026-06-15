<?php

namespace Tests\Feature;

use App\DTO\PostData;
use App\Enums\PostModerationAction;
use App\Models\Post;
use App\Models\PostModerationLog;
use App\Notifications\PostApprovedNotification;
use App\Services\Contracts\PostServiceContract;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс post moderation log.
 */
class PostModerationLogTest extends TestCase
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
     * test approve creates moderation log and notifies author.
     */
    public function test_approve_creates_moderation_log_and_notifies_author(): void
    {
        Notification::fake();
        $author = $this->createAuthorUser(['email' => 'log-author@example.com']);
        $moderator = $this->createModeratorUser(['email' => 'log-mod@example.com']);
        $post = Post::factory()->for($author)->pendingModeration()->create();
        app(PostServiceContract::class)->approve($post, $moderator);
        $this->assertDatabaseHas('post_moderation_logs', ['post_id' => $post->id, 'actor_id' => $moderator->id,
            'action' => PostModerationAction::Approved->value]);
        Notification::assertSentTo($author, PostApprovedNotification::class);
    }

    /**
     * test create for author logs submitted action.
     */
    public function test_create_for_author_logs_submitted_action(): void
    {
        $author = $this->createAuthorUser();
        $post = app(PostServiceContract::class)->createForAuthor(PostData::fromValidated(title: 'Test post title',
            slug: 'test-post-title', excerpt: 'Short excerpt here', body: 'Body content long enough',
            isPublished: false, userId: $author->id), $author);
        $this->assertTrue(PostModerationLog::query()->where('post_id', $post->id)->where('action',
            PostModerationAction::Submitted->value)->exists());
    }
}
