<?php

namespace Tests\Feature;

use App\Enums\PostVisibility;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс conditional get.
 */
class ConditionalGetTest extends TestCase
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
        Cache::flush();
    }

    /**
     * test post show returns cache and robots headers.
     */
    public function test_post_show_returns_cache_and_robots_headers(): void
    {
        $post = Post::factory()->published()->create(['visibility' => PostVisibility::Guest->value]);
        $response = $this->get(route('posts.show', $post));
        $response->assertOk();
        $response->assertHeader('Vary', 'Cookie');
        $this->assertNotNull($response->headers->get('ETag'));
        $this->assertNotNull($response->headers->get('Last-Modified'));
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertIsString($cacheControl);
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('max-age=', $cacheControl);
        $response->assertHeader('X-Robots-Tag', 'index,follow');
    }

    /**
     * test post show returns 304 when etag matches.
     */
    public function test_post_show_returns_304_when_etag_matches(): void
    {
        $post = Post::factory()->published()->create(['visibility' => PostVisibility::Guest->value]);
        $first = $this->get(route('posts.show', $post));
        $etag = $first->headers->get('ETag');
        $this->assertIsString($etag);
        $lastModified = $first->headers->get('Last-Modified');
        $this->assertIsString($lastModified);
        $second = $this->get(route('posts.show', $post), ['If-Modified-Since' => $lastModified]);
        $second->assertStatus(304);
    }

    /**
     * Auth-пользователь: 304 при совпадении ETag на листинге.
     */
    public function test_authenticated_posts_index_returns_304_when_etag_matches(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(2)->for($user)->published()->create(['visibility' => PostVisibility::Guest->value]);
        $first = $this->actingAs($user)->get(route('posts.index'));
        $first->assertOk();
        $etag = $first->headers->get('ETag');
        $this->assertIsString($etag);

        $second = $this->actingAs($user)->get(route('posts.index'), ['If-None-Match' => $etag]);
        $second->assertStatus(304);
    }

    /**
     * test posts index returns cache headers.
     */
    public function test_posts_index_returns_cache_headers(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(2)->for($user)->published()->create(['visibility' => PostVisibility::Guest->value]);
        $response = $this->get(route('posts.index'));
        $response->assertOk();
        $response->assertHeader('Vary', 'Cookie');
        $this->assertNotNull($response->headers->get('ETag'));
        $this->assertNotNull($response->headers->get('Last-Modified'));
    }

    /**
     * test api post show returns cache headers.
     */
    public function test_api_post_show_returns_cache_headers(): void
    {
        $post = Post::factory()->published()->create(['visibility' => PostVisibility::Guest->value]);
        $response = $this->getJson('/api/v1/posts/'.$post->id);
        $response->assertOk();
        $response->assertHeader('Vary', 'Cookie');
        $this->assertNotNull($response->headers->get('ETag'));
    }
}
