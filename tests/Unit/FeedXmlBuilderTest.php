<?php

namespace Tests\Unit;

use App\DTO\FeedEntryData;
use App\Models\Post;
use App\Support\Feed\FeedXmlBuilder;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Unit-тесты сборки RSS/Atom XML через FeedXmlBuilder.
 */
class FeedXmlBuilderTest extends TestCase
{
    private FeedXmlBuilder $builder;

    /**
     * Создаёт экземпляр билдера перед каждым тестом.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new FeedXmlBuilder;
    }

    /**
     * test rss item includes title link and description.
     */
    public function test_rss_item_includes_title_link_and_description(): void
    {
        $post = new Post(['title' => 'Test Post']);
        $post->published_at = Carbon::parse('2024-01-15 12:00:00');
        $entry = new FeedEntryData($post, 'Summary text', 'https://example.com/posts/test');
        $xml = $this->builder->rssItem($entry);
        $this->assertStringContainsString('<item>', $xml);
        $this->assertStringContainsString('<title>Test Post</title>', $xml);
        $this->assertStringContainsString('<link>https://example.com/posts/test</link>', $xml);
        $this->assertStringContainsString('<description>Summary text</description>', $xml);
        $this->assertStringContainsString('<pubDate>', $xml);
    }

    /**
     * test atom entry includes title and summary.
     */
    public function test_atom_entry_includes_title_and_summary(): void
    {
        $post = new Post(['title' => 'Atom Post']);
        $post->published_at = Carbon::parse('2024-01-15 12:00:00');
        $entry = new FeedEntryData($post, 'Atom summary', 'https://example.com/posts/atom');
        $xml = $this->builder->atomEntry($entry);
        $this->assertStringContainsString('<entry>', $xml);
        $this->assertStringContainsString('<title>Atom Post</title>', $xml);
        $this->assertStringContainsString('<summary>Atom summary</summary>', $xml);
        $this->assertStringContainsString('<published>', $xml);
    }

    /**
     * test xml escape encodes special characters.
     */
    public function test_xml_escape_encodes_special_characters(): void
    {
        $this->assertSame('Tom &amp; Jerry', $this->builder->xmlEscape('Tom & Jerry'));
    }
}
