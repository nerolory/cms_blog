<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс post preview.
 */
class PostPreviewTest extends TestCase
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
     * test author can store preview and view signed url.
     */
    public function test_author_can_store_preview_and_view_signed_url(): void
    {
        $author = $this->createAuthorUser();
        $response = $this->actingAs($author)->post(route('posts.preview.store'), ['title' => 'Preview draft title',
            'excerpt' => 'Preview excerpt long enough', 'body' => '<p>Preview body content</p>',
            'editor_mode' => 'simple']);
        $response->assertRedirect();
        $target = $response->headers->get('Location');
        $this->assertIsString($target);
        $previewResponse = $this->actingAs($author)->get($target);
        $previewResponse->assertOk();
        $previewResponse->assertSee('Preview draft title');
        $previewResponse->assertSee(__('posts.preview.banner'));
        $cacheControl = $previewResponse->headers->get('Cache-Control');
        $this->assertIsString($cacheControl);
        foreach (['no-store', 'no-cache', 'must-revalidate', 'private'] as $directive) {
            $this->assertStringContainsString($directive, $cacheControl);
        }
        $previewResponse->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * test invalid signature is rejected.
     */
    public function test_invalid_signature_is_rejected(): void
    {
        $author = $this->createAuthorUser();
        $url = URL::temporarySignedRoute('posts.preview.show', now()->addMinutes(30), ['token' => 'missing-token']);
        $this->actingAs($author)->get($url.'&tampered=1')->assertForbidden();
    }

    /**
     * test guest cannot store preview.
     */
    public function test_guest_cannot_store_preview(): void
    {
        $this->post(route('posts.preview.store'), ['title' => 'Guest preview',
            'excerpt' => 'Preview excerpt long enough', 'body' => '<p>Preview body content</p>',
            'editor_mode' => 'simple'])->assertRedirect(route('login'));
    }

    /**
     * test author can preview existing pending post.
     */
    public function test_author_can_preview_existing_pending_post(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->pendingModeration()->create(['title' => 'Pending original',
            'body' => '<p>Original</p>']);
        $response = $this->actingAs($author)->post(route('posts.preview.store.existing', $post),
            ['title' => 'Pending preview title', 'excerpt' => $post->excerpt ?? 'Preview excerpt long enough',
                'body' => '<p>Updated preview body</p>', 'editor_mode' => 'simple']);
        $response->assertRedirect();
        $target = $response->headers->get('Location');
        $this->assertIsString($target);
        $this->actingAs($author)->get($target)->assertOk()->assertSee('Pending preview title');
    }

    /**
     * test foreign user cannot view another authors preview.
     */
    public function test_foreign_user_cannot_view_another_authors_preview(): void
    {
        $author = $this->createAuthorUser(['email' => 'author-preview@example.com']);
        $other = $this->createAuthorUser(['email' => 'other-preview@example.com']);
        $response = $this->actingAs($author)->post(route('posts.preview.store'), ['title' => 'Private preview',
            'excerpt' => 'Preview excerpt long enough', 'body' => '<p>Private preview body</p>',
            'editor_mode' => 'simple']);
        $target = $response->headers->get('Location');
        $this->assertIsString($target);
        $this->actingAs($other)->get($target)->assertForbidden();
    }

    /**
     * test author cannot forged author in existing post preview.
     */
    public function test_author_cannot_forged_author_in_existing_post_preview(): void
    {
        $author = $this->createAuthorUser(['name' => 'Original Author', 'email' => 'author-preview-idor@example.com']);
        $intruder = $this->createAuthorUser(['name' => 'Forged Author',
            'email' => 'intruder-preview-idor@example.com']);
        $post = Post::factory()->for($author)->pendingModeration()->create(['title' => 'Preview IDOR post',
            'body' => '<p>Original</p>']);
        $response = $this->actingAs($author)->post(route('posts.preview.store.existing', $post),
            ['title' => 'Preview IDOR title', 'excerpt' => $post->excerpt ?? 'Preview excerpt long enough',
                'body' => '<p>Updated preview body</p>', 'editor_mode' => 'simple', 'user_id' => $intruder->id]);
        $response->assertRedirect();
        $target = $response->headers->get('Location');
        $this->assertIsString($target);
        $this->actingAs($author)->get($target)->assertOk()->assertSee('Original Author', false)
            ->assertDontSee('Forged Author', false);
    }
}
