<?php

namespace Tests\Unit;

use App\Enums\PostStatus;
use App\Models\Post;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

/**
 * Класс post is published sync.
 */
class PostIsPublishedSyncTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Legacy is_published column stays in sync with status enum on save.
     */
    public function test_is_published_is_derived_from_status_on_save(): void
    {
        $post = Post::factory()->draft()->create();
        $fresh = $post->fresh();
        $this->assertNotNull($fresh);
        $this->assertFalse($fresh->is_published);
        $post->status = PostStatus::Published;
        $post->save();
        $fresh = $post->fresh();
        $this->assertNotNull($fresh);
        $this->assertTrue($fresh->is_published);
    }
}
