<?php

namespace Tests\Unit;

use App\Enums\PostEditorMode;
use App\Support\HtmlSanitizer;
use App\Support\PostTheme;
use Tests\TestCase;

/**
 * Класс html sanitizer.
 */
class HtmlSanitizerTest extends TestCase
{
    /**
     * test simple mode allows images with storage src.
     */
    public function test_simple_mode_allows_images_with_storage_src(): void
    {
        $html = '<p>Hello</p><img src="/storage/posts/content/test.jpg" alt="Photo">';
        $clean = HtmlSanitizer::sanitize($html, PostEditorMode::Simple);
        $this->assertStringContainsString('<img', $clean);
        $this->assertStringContainsString('/storage/posts/content/test.jpg', $clean);
    }

    /**
     * test simple mode strips external images.
     */
    public function test_simple_mode_strips_external_images(): void
    {
        $html = '<p>Hello</p><img src="https://evil.example/image.jpg" alt="Photo">';
        $clean = HtmlSanitizer::sanitize($html, PostEditorMode::Simple);
        $this->assertStringNotContainsString('<img', $clean);
    }

    /**
     * test pro mode allows tables.
     */
    public function test_pro_mode_allows_tables(): void
    {
        $html = '<table><tr><td>Cell</td></tr></table>';
        $clean = HtmlSanitizer::sanitize($html, PostEditorMode::Pro);
        $this->assertStringContainsString('<table>', $clean);
        $this->assertStringContainsString('<td>', $clean);
    }

    /**
     * test pro mode removes script tags and content.
     */
    public function test_pro_mode_removes_script_tags_and_content(): void
    {
        $html = '<p>Text</p><script>alert("xss")</script><script type="module" src="/evil.js"></script>';
        $clean = HtmlSanitizer::sanitize($html, PostEditorMode::Pro);
        $this->assertStringContainsString('<p>Text</p>', $clean);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('alert', $clean);
        $this->assertStringNotContainsString('evil.js', $clean);
    }

    /**
     * test pro mode removes style tags but keeps inline styles.
     */
    public function test_pro_mode_removes_style_tags_but_keeps_inline_styles(): void
    {
        $html = '<style>body { color: red; }</style><div style="color: blue;">Styled</div>';
        $clean = HtmlSanitizer::sanitize($html, PostEditorMode::Pro);
        $this->assertStringNotContainsString('<style', $clean);
        $this->assertStringNotContainsString('body { color: red; }', $clean);
        $this->assertStringContainsString('style="color: blue', $clean);
        $this->assertStringContainsString('Styled', $clean);
    }

    /**
     * test pro mode strips javascript urls and event handlers.
     */
    public function test_pro_mode_strips_javascript_urls_and_event_handlers(): void
    {
        $html = '<a href="javascript:alert(1)" onclick="alert(1)">Click</a>';
        $clean = HtmlSanitizer::sanitize($html, PostEditorMode::Pro);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringContainsString('Click', $clean);
    }

    /**
     * test pro mode removes script tags inside pre blocks.
     */
    public function test_pro_mode_removes_script_tags_inside_pre_blocks(): void
    {
        $html = '<pre class="line-numbers"><script>alert("xss")</script><code>echo 1;</code></pre>';
        $clean = HtmlSanitizer::sanitize($html, PostEditorMode::Pro);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('alert', $clean);
        $this->assertStringContainsString('echo 1;', $clean);
    }

    /**
     * test blank target links get noopener noreferrer.
     */
    public function test_blank_target_links_get_noopener_noreferrer(): void
    {
        $html = '<a href="https://example.com" target="_blank">External</a>';
        $clean = HtmlSanitizer::sanitize($html, PostEditorMode::Simple);
        $this->assertMatchesRegularExpression('/rel="[^"]*noopener[^"]*"/', $clean);
        $this->assertMatchesRegularExpression('/rel="[^"]*noreferrer[^"]*"/', $clean);
    }

    /**
     * test blank target merges noopener into existing rel.
     */
    public function test_blank_target_merges_noopener_into_existing_rel(): void
    {
        $html = '<a href="https://example.com" target="_blank" rel="nofollow">External</a>';
        $clean = HtmlSanitizer::sanitize($html, PostEditorMode::Simple);
        $this->assertStringContainsString('nofollow', $clean);
        $this->assertMatchesRegularExpression('/rel="[^"]*noopener[^"]*"/', $clean);
        $this->assertMatchesRegularExpression('/rel="[^"]*noreferrer[^"]*"/', $clean);
    }

    /**
     * test links without blank target do not get rel added.
     */
    public function test_links_without_blank_target_do_not_get_rel_added(): void
    {
        $html = '<a href="https://example.com">Internal</a>';
        $clean = HtmlSanitizer::sanitize($html, PostEditorMode::Simple);
        $this->assertStringNotContainsString('rel=', $clean);
    }

    /**
     * test simple mode preserves spoiler blockquote and code.
     */
    public function test_simple_mode_preserves_spoiler_blockquote_and_code(): void
    {
        $html = <<<'HTML'
        <blockquote class="post-quote">Quote</blockquote>
        <details class="mce-accordion" open="open">
            <summary class="mce-accordion-summary">Title</summary>
            <div class="mce-accordion-body"><p>Hidden</p></div>
        </details>
        <pre class="language-php line-numbers"><code><?php
        echo "hi";
        </code></pre>
        HTML;
        $clean = HtmlSanitizer::sanitize($html, PostEditorMode::Simple);
        $this->assertStringContainsString('<blockquote', $clean);
        $this->assertStringContainsString('post-quote', $clean);
        $this->assertStringContainsString('<details', $clean);
        $this->assertStringContainsString('<summary', $clean);
        $this->assertStringContainsString('mce-accordion-body', $clean);
        $this->assertStringContainsString('echo "hi";', $clean);
        $this->assertStringNotContainsString('data-mce-id', $clean);
    }
}
/**
 * Класс post theme.
 */
class PostThemeTest extends TestCase
{
    /**
     * test content opacity is clamped to minimum fifty.
     */
    public function test_content_opacity_is_clamped_to_minimum_fifty(): void
    {
        $this->assertSame(50, PostTheme::normalizeOpacity(10));
        $this->assertSame(100, PostTheme::normalizeOpacity(150));
    }

    /**
     * test contrasting text opposes surface brightness.
     */
    public function test_contrasting_text_opposes_surface_brightness(): void
    {
        $this->assertSame('#1a1a1a', PostTheme::contrastingText('#ffffff'));
        $this->assertSame('#f5f5f5', PostTheme::contrastingText('#1a1a1a'));
    }
}
