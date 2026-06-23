<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Smoke tests for Filament /admin access.
 */
class FilamentAdminTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    /**
     * test author cannot access admin panel.
     */
    public function test_author_cannot_access_admin_panel(): void
    {
        $author = $this->createAuthorUser();
        $this->actingAs($author)->get('/admin')->assertForbidden();
    }

    /**
     * test admin can access posts list.
     */
    public function test_admin_can_access_posts_list(): void
    {
        $this->seedRoles();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin)->get('/admin/posts')->assertOk();
    }

    /**
     * test moderator can access posts list.
     */
    public function test_moderator_can_access_posts_list(): void
    {
        $this->seedRoles();
        $moderator = User::factory()->create();
        $moderator->assignRole('moderator');
        $this->actingAs($moderator)->get('/admin/posts')->assertOk();
    }

    /**
     * test admin can access users resource.
     */
    public function test_admin_can_access_users_resource(): void
    {
        $this->seedRoles();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin)->get('/admin/users')->assertOk();
    }

    /**
     * test moderator cannot access users resource.
     */
    public function test_moderator_cannot_access_users_resource(): void
    {
        $this->seedRoles();
        $moderator = User::factory()->create();
        $moderator->assignRole('moderator');
        $this->actingAs($moderator)->get('/admin/users')->assertForbidden();
    }

    /**
     * В админке есть ссылка «На сайт» рядом с логотипом (сайдбар
     * и топбар).
     */
    public function test_admin_topbar_shows_public_site_link(): void
    {
        $this->seedRoles();
        $moderator = User::factory()->create();
        $moderator->assignRole('moderator');

        $this->actingAs($moderator)
            ->get('/admin')
            ->assertOk()
            ->assertSee(__('layout.nav.public_site'), false)
            ->assertSee(route('home'), false)
            ->assertSee('fi-public-site-link', false);
    }

    /**
     * test admin can access shield roles.
     */
    public function test_admin_can_access_shield_roles(): void
    {
        $this->seedRoles();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin)->get('/admin/shield/roles')->assertOk();
    }

    /**
     * test posts index uses lang strings.
     */
    public function test_posts_index_uses_lang_strings(): void
    {
        $this->seedRoles();
        $response = $this->get(route('posts.index'));
        $response->assertOk()->assertSee(__('posts.web.title'), false);
    }
}
