<?php

namespace Tests\Integration;

use App\DTO\SearchFilters;
use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Repositories\Search\DatabaseSearchBackend;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\FakesSiteOperational;

/**
 * Класс postgres full text search.
 */
class PostgresFullTextSearchTest extends PostgresTestCase
{
    use FakesSiteOperational;

    /**
     * test posts table has tsvector column and gin index.
     */
    public function test_posts_table_has_tsvector_column_and_gin_index(): void
    {
        $this->assertSame('pgsql', DB::getDriverName());
        $column = DB::selectOne('
            SELECT data_type
            FROM information_schema.columns
            WHERE table_name = \'posts\' AND column_name = \'search_vector\'
        ');
        $this->assertNotNull($column);
        $this->assertIsObject($column);
        $this->assertTrue(property_exists($column, 'data_type'));
        $this->assertSame('tsvector', $column->data_type);
        $index = DB::selectOne('
            SELECT indexname
            FROM pg_indexes
            WHERE tablename = \'posts\' AND indexname = \'posts_search_vector_gin_index\'
        ');
        $this->assertNotNull($index);
    }

    /**
     * test database search backend uses tsvector ranking.
     */
    public function test_database_search_backend_uses_tsvector_ranking(): void
    {
        $category = Category::query()->where('slug', 'general')->firstOrFail();
        $author = $this->createAuthorUser();
        $matching = Post::factory()->for($author)->published()->create(['title' => 'PostgreSQL Tsvector Unique Marker',
            'excerpt' => 'GIN index verification excerpt', 'category_id' => $category->id,
            'status' => PostStatus::Published]);
        Post::factory()->for($author)->published()->create(['title' => 'Unrelated content beta',
            'category_id' => $category->id, 'status' => PostStatus::Published]);
        $backend = app(DatabaseSearchBackend::class);
        $results = $backend->search(new SearchFilters(query: 'Tsvector', page: 1, perPage: 10), $author);
        $this->assertTrue($results->contains(fn (Post $post): bool => $post->is($matching)));
        $this->assertFalse($results->contains(fn (Post $post): bool => $post->title === 'Unrelated content beta'));
    }
}
