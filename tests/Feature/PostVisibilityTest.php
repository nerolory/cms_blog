<?php

namespace Tests\Feature;

use App\Models\Post;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс post visibility.
 */
class PostVisibilityTest extends TestCase
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
     * Guest sees only posts with guest visibility.
     */
    public function test_guest_sees_only_guest_visible_posts(): void
    {
        Post::factory()->published()->create(['title' => 'Публичная']);
        Post::factory()->published()->authenticatedVisibility()->create(['title' => 'Для auth']);
        $response = $this->get(route('posts.index'));
        $response->assertOk();
        $response->assertSee('Публичная');
        $response->assertDontSee('Для auth');
    }

    /**
     * Authenticated user sees guest and authenticated posts.
     */
    public function test_authenticated_user_sees_authenticated_posts(): void
    {
        $user = $this->createAuthorUser();
        Post::factory()->published()->create(['title' => 'Публичная']);
        Post::factory()->published()->authenticatedVisibility()->create(['title' => 'Для auth']);
        $response = $this->actingAs($user)->get(route('posts.index'));
        $response->assertOk();
        $response->assertSee('Публичная');
        $response->assertSee('Для auth');
    }

    /**
     * Permission-restricted post returns 404 for guests.
     */
    public function test_permission_post_returns_not_found_for_guest(): void
    {
        $post = Post::factory()->published()->withPermissionVisibility('posts.view.shareholders')->create();
        $this->get(route('posts.show', $post))->assertNotFound();
    }

    /**
     * User with the required permission sees the restricted post.
     */
    public function test_user_with_permission_sees_restricted_post(): void
    {
        $user = $this->createAuthorUser();
        $user->givePermissionTo('posts.view.shareholders');
        $post = Post::factory()->published()->withPermissionVisibility('posts.view.shareholders')
            ->create(['title' => 'Для акционеров']);
        $this->actingAs($user)->get(route('posts.show', $post))->assertOk()->assertSee('Для акционеров');
    }

    /**
     * Author can view their own pending post.
     */
    public function test_author_can_view_own_pending_post(): void
    {
        $user = $this->createAuthorUser();
        $post = Post::factory()->for($user)->pendingModeration()->create(['title' => 'На модерации']);
        $this->actingAs($user)->get(route('posts.show', $post))->assertOk()->assertSee('На модерации');
    }
}
