<?php

namespace Tests\Feature;

use App\Enums\PostVisibility;
use App\Models\Post;
use App\Models\User;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс seo.
 */
class SeoTest extends TestCase
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
     * test sitemap returns xml with published posts.
     */
    public function test_sitemap_returns_xml_with_published_posts(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'sitemap-post',
            'visibility' => PostVisibility::Guest->value]);
        $response = $this->get(route('seo.sitemap'));
        $response->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString(route('posts.show', $post), $content);
    }

    /**
     * test robots txt contains sitemap and disallow rules.
     */
    public function test_robots_txt_contains_sitemap_and_disallow_rules(): void
    {
        $response = $this->get(route('seo.robots'));
        $response->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $body = $response->getContent();
        $this->assertIsString($body);
        $this->assertStringContainsString('Sitemap: '.url('sitemap.xml'), $body);
        $this->assertStringContainsString('Disallow: /admin', $body);
        $this->assertStringContainsString('Disallow: /posts/preview/', $body);
    }

    /**
     * test show page renders seo meta and json ld.
     */
    public function test_show_page_renders_seo_meta_and_json_ld(): void
    {
        $author = User::factory()->create(['name' => 'Meta Author']);
        $post = Post::factory()->for($author)->published()->create(['title' => 'Visible SEO Post',
            'excerpt' => 'Visible excerpt for search engines', 'meta_title' => 'Custom meta title',
            'meta_description' => 'Custom meta description', 'visibility' => PostVisibility::Guest->value]);
        $response = $this->get(route('posts.show', $post));
        $response->assertOk();
        $response->assertSee('Custom meta title', false);
        $response->assertSee('meta name="description" content="Custom meta description"', false);
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"Article"', false);
        $response->assertSee('Visible SEO Post', false);
    }
}
