<?php

namespace Tests\Feature;

use App\Models\Post;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс post policy.
 */
class PostPolicyTest extends TestCase
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
     * test author cannot update someone elses post.
     */
    public function test_author_cannot_update_someone_elses_post(): void
    {
        $owner = $this->createAuthorUser(['email' => 'owner-policy@example.com']);
        $intruder = $this->createAuthorUser(['email' => 'intruder@example.com']);
        $post = Post::factory()->for($owner)->pendingModeration()->create();
        $this->assertFalse($intruder->can('update', $post));
    }

    /**
     * test author can update own pending post.
     */
    public function test_author_can_update_own_pending_post(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->pendingModeration()->create();
        $this->assertTrue($author->can('update', $post));
    }

    /**
     * test author can update own published post.
     */
    public function test_author_can_update_own_published_post(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->published()->create();
        $this->assertTrue($author->can('update', $post));
    }

    /**
     * test moderator can moderate pending post.
     */
    public function test_moderator_can_moderate_pending_post(): void
    {
        $author = $this->createAuthorUser(['email' => 'author-mod@example.com']);
        $moderator = $this->createModeratorUser(['email' => 'mod@example.com']);
        $post = Post::factory()->for($author)->pendingModeration()->create();
        $this->assertTrue($moderator->can('moderate', $post));
    }

    /**
     * test moderator can update pending post.
     */
    public function test_moderator_can_update_pending_post(): void
    {
        $author = $this->createAuthorUser(['email' => 'author-mod-update@example.com']);
        $moderator = $this->createModeratorUser(['email' => 'mod-update@example.com']);
        $post = Post::factory()->for($author)->pendingModeration()->create();
        $this->assertTrue($moderator->can('update', $post));
    }

    /**
     * test owner can update published post.
     */
    public function test_owner_can_update_published_post(): void
    {
        $author = $this->createAuthorUser(['email' => 'author-owner-update@example.com']);
        $owner = $this->createOwnerUser(['email' => 'owner-update@example.com']);
        $post = Post::factory()->for($author)->published()->create();
        $this->assertTrue($owner->can('update', $post));
    }

    /**
     * test author cannot access edit form for foreign post.
     */
    public function test_author_cannot_access_edit_form_for_foreign_post(): void
    {
        $owner = $this->createAuthorUser(['email' => 'owner-edit@example.com']);
        $intruder = $this->createAuthorUser(['email' => 'intruder-edit@example.com']);
        $post = Post::factory()->for($owner)->pendingModeration()->create();
        $this->actingAs($intruder)->get(route('posts.edit', $post))->assertForbidden();
    }
}
