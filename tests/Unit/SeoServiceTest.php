<?php

namespace Tests\Unit;

use App\DTO\SeoData;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Post;
use App\Models\User;
use App\Services\Contracts\SeoServiceContract;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс seo service.
 */
class SeoServiceTest extends TestCase
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
     * test resolve for post uses custom meta and fallbacks.
     */
    public function test_resolve_for_post_uses_custom_meta_and_fallbacks(): void
    {
        $author = User::factory()->create(['name' => 'SEO Author']);
        $post = Post::factory()->for($author)->published()->create(['title' => 'Post title',
            'excerpt' => 'Post excerpt for description fallback', 'meta_title' => 'Custom SEO title',
            'meta_description' => 'Custom SEO description', 'visibility' => PostVisibility::Guest->value]);
        $seo = app(SeoServiceContract::class)->resolveForPost($post);
        $this->assertSame('Custom SEO title', $seo->title);
        $this->assertSame('Custom SEO description', $seo->description);
        $this->assertSame('index,follow', $seo->robots);
        $this->assertSame('SEO Author', $seo->authorName);
        $this->assertSame('Post title', $seo->headline);
        $this->assertSame('Article', $seo->jsonLdArticle()['@type']);
    }

    /**
     * test resolve for post uses noindex for non guest visibility.
     */
    public function test_resolve_for_post_uses_noindex_for_non_guest_visibility(): void
    {
        $post = Post::factory()->published()->create(['visibility' => PostVisibility::Authenticated->value]);
        $seo = app(SeoServiceContract::class)->resolveForPost($post);
        $this->assertSame('noindex,nofollow', $seo->robots);
    }

    /**
     * test resolve for preview forces noindex.
     */
    public function test_resolve_for_preview_forces_noindex(): void
    {
        $post = Post::factory()->published()->create(['visibility' => PostVisibility::Guest->value]);
        $seo = app(SeoServiceContract::class)->resolveForPreview($post);
        $this->assertSame('noindex,nofollow', $seo->robots);
    }

    /**
     * test build sitemap contains published guest posts only.
     */
    public function test_build_sitemap_contains_published_guest_posts_only(): void
    {
        $included = Post::factory()->published()->create(['slug' => 'public-seo-post',
            'visibility' => PostVisibility::Guest->value]);
        Post::factory()->published()->create(['visibility' => PostVisibility::Authenticated->value]);
        Post::factory()->create(['status' => PostStatus::Draft, 'visibility' => PostVisibility::Guest->value]);
        $xml = app(SeoServiceContract::class)->buildSitemapXml();
        $this->assertStringContainsString(route('posts.show', $included), $xml);
        $this->assertStringContainsString('<urlset', $xml);
    }

    /**
     * test update for post persists seo fields.
     */
    public function test_update_for_post_persists_seo_fields(): void
    {
        $post = Post::factory()->published()->create();
        app(SeoServiceContract::class)->updateForPost($post, new SeoData(metaTitle: 'Updated title',
            metaDescription: 'Updated description', ogImagePath: 'posts/og/test.jpg',
            canonicalUrl: 'https://example.test/custom', robots: 'noindex,follow'));
        $post->refresh();
        $this->assertSame('Updated title', $post->meta_title);
        $this->assertSame('Updated description', $post->meta_description);
        $this->assertSame('posts/og/test.jpg', $post->og_image_path);
        $this->assertSame('https://example.test/custom', $post->canonical_url);
        $this->assertSame('noindex,follow', $post->robots);
    }
}
