<?php

namespace Tests\Feature;

use App\DTO\PostData;
use App\Enums\PostModerationAction;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Post;
use App\Models\PostVersion;
use App\Services\Contracts\PostServiceContract;
use App\Services\Contracts\PostVersionServiceContract;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс post version.
 */
class PostVersionTest extends TestCase
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
     * test update creates version snapshot.
     */
    public function test_update_creates_version_snapshot(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->create(['title' => 'Original title', 'status' => PostStatus::Draft]);
        $this->actingAs($author)->put(route('posts.update', $post), ['title' => 'Updated title', 'slug' => $post->slug,
            'excerpt' => $post->excerpt, 'body' => $post->body, 'editor_mode' => $post->editor_mode->value,
            'visibility' => PostVisibility::Guest->value])->assertRedirect();
        $this->assertDatabaseHas('post_versions', ['post_id' => $post->id, 'version_number' => 1]);
        $version = PostVersion::query()->where('post_id', $post->id)->first();
        $this->assertNotNull($version);
        $this->assertSame('Original title', $version->snapshot['title'] ?? null);
    }

    /**
     * test fifo prunes to two versions.
     */
    public function test_fifo_prunes_to_two_versions(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->create(['title' => 'Version base', 'status' => PostStatus::Draft]);
        foreach (['Second', 'Third', 'Fourth'] as $suffix) {
            $this->actingAs($author)->put(route('posts.update', $post), ['title' => "Title {$suffix}",
                'slug' => $post->slug, 'excerpt' => $post->excerpt, 'body' => $post->body,
                'editor_mode' => $post->editor_mode->value, 'visibility' => PostVisibility::Guest->value]);
            $post->refresh();
        }
        $this->assertSame(2, PostVersion::query()->where('post_id', $post->id)->count());
        $numbers = PostVersion::query()->where('post_id',
            $post->id)->orderBy('version_number')->pluck('version_number')->all();
        $this->assertSame([2, 3], $numbers);
    }

    /**
     * test restore reverts post and logs moderation.
     */
    public function test_restore_reverts_post_and_logs_moderation(): void
    {
        $moderator = $this->createOwnerUser();
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->create(['title' => 'Before change', 'status' => PostStatus::Draft]);
        $this->actingAs($author)->put(route('posts.update', $post), ['title' => 'After change', 'slug' => $post->slug,
            'excerpt' => $post->excerpt, 'body' => $post->body, 'editor_mode' => $post->editor_mode->value,
            'visibility' => PostVisibility::Guest->value]);
        $version = PostVersion::query()->where('post_id', $post->id)->first();
        $this->assertNotNull($version);
        app(PostVersionServiceContract::class)->restore($version, $moderator);
        $post->refresh();
        $this->assertSame('Before change', $post->title);
        $this->assertDatabaseHas('post_moderation_logs', ['post_id' => $post->id,
            'action' => PostModerationAction::Restored->value, 'actor_id' => $moderator->id]);
    }

    /**
     * test edit page shows version history.
     */
    public function test_edit_page_shows_version_history(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->create(['title' => 'Listed version', 'status' => PostStatus::Draft]);
        app(PostServiceContract::class)->updateFromWeb(PostData::fromValidated(title: 'Changed', slug: $post->slug,
            excerpt: $post->excerpt ?? '', body: $post->body, isPublished: false, userId: $author->id,
            status: PostStatus::Draft, visibility: $post->visibility, requiredPermissionId: null,
            featuredImagePath: null, backgroundImagePath: null, themePrimaryColor: null, themeAccentColor: null,
            contentOpacity: 100, editorMode: $post->editor_mode), $post, $author);
        $this->actingAs($author)->get(route('posts.edit',
            $post))->assertOk()->assertSee(__('posts.versions.title'))->assertSee('Listed version');
    }
}
