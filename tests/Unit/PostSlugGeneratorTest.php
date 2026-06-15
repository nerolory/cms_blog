<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Support\PostSlugGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Класс post slug generator.
 */
class PostSlugGeneratorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * test generates slug from title.
     */
    public function test_generates_slug_from_title(): void
    {
        $slug = PostSlugGenerator::fromTitle('Hello World Post');
        $this->assertSame('hello-world-post', $slug);
    }

    /**
     * test appends suffix when slug already exists.
     */
    public function test_appends_suffix_when_slug_already_exists(): void
    {
        Post::factory()->create(['slug' => 'duplicate-title']);
        $slug = PostSlugGenerator::fromTitle('Duplicate Title');
        $this->assertSame('duplicate-title-2', $slug);
    }

    /**
     * test uses custom slug when provided.
     */
    public function test_uses_custom_slug_when_provided(): void
    {
        $slug = PostSlugGenerator::fromTitle('Any title', 'custom-slug');
        $this->assertSame('custom-slug', $slug);
    }
}
