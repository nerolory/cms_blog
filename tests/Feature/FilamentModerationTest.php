<?php

namespace Tests\Feature;

use App\Models\Post;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс filament moderation.
 */
class FilamentModerationTest extends TestCase
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
     * test moderator can open posts list with pending tab.
     */
    public function test_moderator_can_open_posts_list_with_pending_tab(): void
    {
        $author = $this->createAuthorUser(['email' => 'author-fil@example.com']);
        $moderator = $this->createModeratorUser(['email' => 'mod-fil@example.com']);
        Post::factory()->for($author)->pendingModeration()->create(['title' => 'На модерации Filament']);
        $this->actingAs($moderator)->get('/admin/posts?tab=pending')->assertOk()
            ->assertSee('На модерации Filament', false);
    }

    /**
     * test admin can open login page.
     */
    public function test_admin_can_open_login_page(): void
    {
        $this->get('/admin/login')->assertOk();
    }
}
