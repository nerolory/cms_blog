<?php

namespace Tests\Feature;

use App\Enums\SearchDriver;
use App\Events\PostPublished;
use App\Jobs\IndexPostForSearchJob;
use App\Models\Category;
use App\Models\Post;
use App\Repositories\Contracts\SearchSettingsRepositoryContract;
use App\Services\Search\SearchBackendResolver;
use App\Support\Search\SearchSettingKey;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс search backend fallback.
 */
class SearchBackendFallbackTest extends TestCase
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
     * test resolver falls back to database when elasticsearch unavailable.
     */
    public function test_resolver_falls_back_to_database_when_elasticsearch_unavailable(): void
    {
        $this->setConfiguredDriver(SearchDriver::Elasticsearch);
        config(['search.backends.elasticsearch.enabled' => false]);
        $resolver = app(SearchBackendResolver::class);
        $this->assertSame(SearchDriver::Elasticsearch, $resolver->configuredDriver());
        $this->assertSame(SearchDriver::Database, $resolver->resolvedDriver());
        $this->assertSame(SearchDriver::Database, $resolver->resolve()->driver());
    }

    /**
     * test resolver uses configured backend when available.
     */
    public function test_resolver_uses_configured_backend_when_available(): void
    {
        $this->setConfiguredDriver(SearchDriver::Elasticsearch);
        config(['search.backends.elasticsearch.enabled' => true]);
        $resolver = app(SearchBackendResolver::class);
        $this->assertSame(SearchDriver::Elasticsearch, $resolver->resolvedDriver());
    }

    /**
     * test search endpoint falls back to database results.
     */
    public function test_search_endpoint_falls_back_to_database_results(): void
    {
        $this->setConfiguredDriver(SearchDriver::Solr);
        config(['search.backends.solr.enabled' => false]);
        $category = Category::query()->where('slug', 'general')->firstOrFail();
        $author = $this->createAuthorUser();
        $matching = Post::factory()->for($author)->published()->create(['title' => 'Solr Fallback Unique Title',
            'category_id' => $category->id]);
        Post::factory()->for($author)->published()->create(['title' => 'Unrelated beta post',
            'category_id' => $category->id]);
        $response = $this->get(route('search.index', ['q' => 'Fallback Unique']));
        $response->assertOk();
        $response->assertSee($matching->title);
        $response->assertDontSee('Unrelated beta post');
    }

    /**
     * test post publish queues search index job.
     */
    public function test_post_publish_queues_search_index_job(): void
    {
        Queue::fake();
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->published()->create();
        event(new PostPublished($post, $author));
        Queue::assertPushed(IndexPostForSearchJob::class,
            fn (IndexPostForSearchJob $job): bool => $job->postId === $post->id);
    }

    private function setConfiguredDriver(SearchDriver $driver): void
    {
        app(SearchSettingsRepositoryContract::class)->set(SearchSettingKey::DRIVER, $driver->value);
    }
}
