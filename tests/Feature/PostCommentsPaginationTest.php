<?php

namespace Tests\Feature;

use App\Enums\CommentStatus;
use App\Models\Post;
use App\Models\PostComment;
use App\Repositories\Contracts\CommentRepositoryContract;
use App\Services\CommentService;
use App\Services\Contracts\CommentServiceContract;
use App\Support\TypeCast;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Пагинация root-комментариев на show.
 */
class PostCommentsPaginationTest extends TestCase
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
     * test root comments api returns offset twenty with has more flag.
     */
    public function test_root_comments_api_returns_offset_twenty_with_has_more_flag(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->published()->create();

        for ($index = 0; $index < 25; $index++) {
            PostComment::query()->create([
                'post_id' => $post->id,
                'user_id' => $author->id,
                'body' => 'Root '.$index,
                'parent_id' => null,
                'status' => CommentStatus::Visible,
            ]);
        }

        $firstPage = $this->getJson(route('posts.comments.index', $post));
        $firstPage->assertOk();
        $firstPage->assertJsonPath('has_more', true);
        $firstPage->assertJsonPath('next_offset', CommentService::ROOT_PAGE_SIZE);

        $secondPage = $this->getJson(route('posts.comments.index', [$post, 'offset' => 20]));
        $secondPage->assertOk();
        $secondPage->assertJsonPath('has_more', false);
        $secondPage->assertJsonPath('next_offset', 25);
        $this->assertSame(5, substr_count(TypeCast::string($secondPage->json('html')), 'data-comment-thread'));
    }

    /**
     * test root comments are ordered newest first.
     */
    public function test_root_comments_are_ordered_newest_first(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->published()->create();
        $older = PostComment::query()->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
            'body' => 'Older root',
            'parent_id' => null,
            'status' => CommentStatus::Visible,
        ]);
        $newer = PostComment::query()->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
            'body' => 'Newer root',
            'parent_id' => null,
            'status' => CommentStatus::Visible,
        ]);
        PostComment::query()->whereKey($older->id)->update(['created_at' => now()->subHour()]);
        PostComment::query()->whereKey($newer->id)->update(['created_at' => now()]);
        app(CommentServiceContract::class)->forgetSectionCacheForPost($post->id);

        $roots = app(CommentRepositoryContract::class)
            ->getVisibleRootCommentsForPost($post->id, 20, 0);
        $this->assertSame('Newer root', $roots->first()?->body);
        $this->assertSame('Older root', $roots->last()?->body);

        $response = $this->getJson(route('posts.comments.index', $post));
        $html = TypeCast::string($response->json('html'));
        $this->assertMatchesRegularExpression('/Newer root[\s\S]*Older root/', $html);

        $show = (string) $this->get(route('posts.show', $post))->getContent();
        $this->assertMatchesRegularExpression('/Newer root[\s\S]*Older root/', $show);
    }

    /**
     * test thread replies stay ordered oldest first.
     */
    public function test_thread_replies_stay_ordered_oldest_first(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->published()->create();
        $root = PostComment::query()->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
            'body' => 'Root',
            'parent_id' => null,
            'status' => CommentStatus::Visible,
        ]);
        PostComment::query()->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
            'body' => 'Older reply',
            'parent_id' => $root->id,
            'status' => CommentStatus::Visible,
        ]);
        $newerReply = PostComment::query()->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
            'body' => 'Newer reply',
            'parent_id' => $root->id,
            'status' => CommentStatus::Visible,
        ]);
        PostComment::query()->where('post_id', $post->id)->where('body', 'Older reply')->update([
            'created_at' => now()->subMinutes(10),
        ]);
        PostComment::query()->whereKey($newerReply->id)->update(['created_at' => now()]);

        $replies = app(CommentRepositoryContract::class)
            ->getVisibleThreadReplies($root->id, 20, 0);
        $this->assertSame('Older reply', $replies->first()?->body);
        $this->assertSame('Newer reply', $replies->last()?->body);

        $response = $this->getJson(route('posts.comments.thread', [$post, $root->id]));
        $html = TypeCast::string($response->json('html'));
        $this->assertMatchesRegularExpression('/Older reply[\s\S]*Newer reply/', $html);
    }
}
