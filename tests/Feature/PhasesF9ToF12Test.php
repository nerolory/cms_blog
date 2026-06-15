<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\ReactionType;
use App\Jobs\RunSiteHealthCheckJob;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostComment;
use App\Services\Contracts\ScheduledPublishServiceContract;
use App\Services\Contracts\SiteHealthServiceContract;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс phases f9to f12.
 */
class PhasesF9ToF12Test extends TestCase
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
     * test site health service runs checks.
     */
    public function test_site_health_service_runs_checks(): void
    {
        $service = app(SiteHealthServiceContract::class);
        $report = $service->runChecks(null);
        $this->assertGreaterThan(0, $report->results->count());
        $stored = $service->persistReport($report);
        $this->assertDatabaseHas('site_health_reports', ['id' => $stored->id]);
    }

    /**
     * test search filters by category and query.
     */
    public function test_search_filters_by_category_and_query(): void
    {
        $category = Category::query()->where('slug', 'general')->firstOrFail();
        $author = $this->createAuthorUser();
        $matching = Post::factory()->for($author)->published()->create(['title' => 'Unique Searchable Alpha Title',
            'category_id' => $category->id]);
        Post::factory()->for($author)->published()->create(['title' => 'Other post beta',
            'category_id' => $category->id]);
        $response = $this->get(route('search.index', ['q' => 'Alpha', 'category' => $category->id]));
        $response->assertOk();
        $response->assertSee($matching->title);
        $response->assertDontSee('Other post beta');
    }

    /**
     * test comment depth two and reactions.
     */
    public function test_comment_depth_two_and_reactions(): void
    {
        $author = $this->createAuthorUser();
        $commenter = $this->createAuthorUser(['email' => 'commenter@example.com']);
        $post = Post::factory()->for($author)->published()->create();
        $this->actingAs($commenter)->post(route('posts.comments.store', $post),
            ['body' => 'Root comment'])->assertRedirect();
        $root = PostComment::query()->where('post_id', $post->id)->whereNull('parent_id')->firstOrFail();
        $this->actingAs($commenter)->post(route('posts.comments.store', $post), ['body' => 'Reply level 1',
            'parent_id' => $root->id])->assertRedirect();
        $reply = PostComment::query()->where('parent_id', $root->id)->firstOrFail();
        $this->actingAs($commenter)->post(route('posts.comments.store', $post), ['body' => 'Should fail depth 3',
            'parent_id' => $reply->id])->assertSessionHasErrors();
        $this->actingAs($commenter)->post(route('posts.reactions.store', $post),
            ['type' => ReactionType::Like->value])->assertRedirect();
        $this->actingAs($commenter)->post(route('posts.reactions.store', $post),
            ['type' => ReactionType::Like->value])->assertRedirect();
        $this->assertDatabaseMissing('post_reactions', ['post_id' => $post->id, 'user_id' => $commenter->id]);
    }

    /**
     * test post show displays engagement section.
     */
    public function test_post_show_displays_engagement_section(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->published()
            ->create(['body' => '<h2>Section</h2><p>Content paragraph here.</p>']);
        $response = $this->get(route('posts.show', $post));
        $response->assertOk();
        $response->assertSee('Section');
        $response->assertSee(__('engagement.comments.title'));
    }

    /**
     * test rss feed returns xml.
     */
    public function test_rss_feed_returns_xml(): void
    {
        $author = $this->createAuthorUser();
        Post::factory()->for($author)->published()->create();
        $response = $this->get(route('feed.rss'));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
        $response->assertSee('<rss', false);
    }

    /**
     * test author page lists published posts.
     */
    public function test_author_page_lists_published_posts(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->published()->create();
        $response = $this->get(route('authors.show', $author));
        $response->assertOk();
        $response->assertSee($post->title);
    }

    /**
     * test scheduled publish publishes due posts.
     */
    public function test_scheduled_publish_publishes_due_posts(): void
    {
        $owner = $this->createOwnerUser();
        $post = Post::factory()->for($owner)->pendingModeration()->create(['scheduled_publish_at' => now()
            ->subMinute()]);
        $count = app(ScheduledPublishServiceContract::class)->publishDuePosts();
        $this->assertSame(1, $count);
        $post->refresh();
        $this->assertSame(PostStatus::Published, $post->status);
    }

    /**
     * test site health job can be queued.
     */
    public function test_site_health_job_can_be_queued(): void
    {
        Queue::fake();
        RunSiteHealthCheckJob::dispatch(1);
        Queue::assertPushed(RunSiteHealthCheckJob::class);
    }
}
