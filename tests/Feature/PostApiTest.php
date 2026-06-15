<?php

namespace Tests\Feature;

use App\Models\Post;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс post api.
 */
class PostApiTest extends TestCase
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
     * test guest can list public posts.
     */
    public function test_guest_can_list_public_posts(): void
    {
        Post::factory()->published()->create(['title' => 'Public API post']);
        $response = $this->getJson('/api/v1/posts');
        $response->assertOk()->assertJsonPath('data.0.title', 'Public API post');
    }

    /**
     * test authenticated user can create post via api.
     */
    public function test_authenticated_user_can_create_post_via_api(): void
    {
        $user = $this->createAuthorUser();
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/posts', ['title' => 'API created post', 'slug' => 'api-created-post',
            'excerpt' => 'Excerpt for api post', 'body' => 'Body content for api post']);
        $response->assertCreated()->assertJsonPath('data.slug', 'api-created-post');
        $this->assertDatabaseHas('posts', ['slug' => 'api-created-post', 'user_id' => $user->id]);
    }

    /**
     * test user cannot update foreign post via api.
     */
    public function test_user_cannot_update_foreign_post_via_api(): void
    {
        $owner = $this->createAuthorUser(['email' => 'owner-api@example.com']);
        $intruder = $this->createAuthorUser(['email' => 'intruder-api@example.com']);
        $post = Post::factory()->for($owner)->pendingModeration()->create();
        Sanctum::actingAs($intruder);
        $this->patchJson('/api/v1/posts/'.$post->id, ['title' => 'Hacked title here',
            'body' => 'Hacked body content'])->assertForbidden();
    }
}
