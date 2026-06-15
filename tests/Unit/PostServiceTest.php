<?php

namespace Tests\Unit;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\Contracts\PostServiceContract;
use Illuminate\Auth\Access\AuthorizationException;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс post service.
 */
class PostServiceTest extends TestCase
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
     * test approve throws when user lacks moderate permission.
     */
    public function test_approve_throws_when_user_lacks_moderate_permission(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->pendingModeration()->create();
        $this->expectException(AuthorizationException::class);
        app(PostServiceContract::class)->approve($post, $author);
    }

    /**
     * test reject throws when user lacks moderate permission.
     */
    public function test_reject_throws_when_user_lacks_moderate_permission(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->pendingModeration()->create();
        $this->expectException(AuthorizationException::class);
        app(PostServiceContract::class)->reject($post, $author, 'Причина');
    }

    /**
     * test approve transitions post to published.
     */
    public function test_approve_transitions_post_to_published(): void
    {
        $author = $this->createAuthorUser(['email' => 'author-svc@example.com']);
        $moderator = $this->createModeratorUser(['email' => 'mod-svc@example.com']);
        $post = Post::factory()->for($author)->pendingModeration()->create();
        $approved = app(PostServiceContract::class)->approve($post, $moderator);
        $this->assertSame(PostStatus::Published, $approved->status);
        $this->assertTrue($approved->is_published);
    }
}
