<?php

namespace Tests\Unit;

use App\Enums\PostStatus;
use App\Models\Post;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

/**
 * Класс post factory.
 */
class PostFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Published state syncs status, is_published, and published_at.
     */
    public function test_published_state_syncs_status_and_flags(): void
    {
        $post = Post::factory()->published()->create();
        $this->assertSame(PostStatus::Published, $post->status);
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
        $this->assertNull($post->rejection_reason);
        $this->assertSame('guest', $post->visibility);
    }

    /**
     * pending_moderation state does not publish the post.
     */
    public function test_pending_moderation_state(): void
    {
        $post = Post::factory()->pendingModeration()->create();
        $this->assertSame(PostStatus::PendingModeration, $post->status);
        $this->assertFalse($post->is_published);
        $this->assertNull($post->published_at);
        $this->assertNull($post->rejection_reason);
    }

    /**
     * Rejected state stores the reason and unpublishes the post.
     */
    public function test_rejected_state(): void
    {
        $post = Post::factory()->rejected()->create();
        $this->assertSame(PostStatus::Rejected, $post->status);
        $this->assertFalse($post->is_published);
        $this->assertNull($post->published_at);
        $this->assertNotEmpty($post->rejection_reason);
    }

    /**
     * Draft state keeps the post unpublished.
     */
    public function test_draft_state(): void
    {
        $post = Post::factory()->draft()->create();
        $this->assertSame(PostStatus::Draft, $post->status);
        $this->assertFalse($post->is_published);
        $this->assertNull($post->published_at);
        $this->assertNull($post->rejection_reason);
    }

    /**
     * Permission visibility uses FK to permissions table.
     */
    public function test_permission_visibility_format(): void
    {
        Permission::create(['name' => 'posts.view.shareholders', 'guard_name' => 'web']);
        $post = Post::factory()->withPermissionVisibility('posts.view.shareholders')->create();
        $this->assertSame('permission', $post->visibility);
        $this->assertNotNull($post->required_permission_id);
    }

    /**
     * Factory slugs are unique across records.
     */
    public function test_slug_is_unique(): void
    {
        $first = Post::factory()->create();
        $second = Post::factory()->create();
        $this->assertNotSame($first->slug, $second->slug);
    }
}
