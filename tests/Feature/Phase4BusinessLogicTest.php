<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Services\Contracts\UserServiceContract;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс phase4business logic.
 */
class Phase4BusinessLogicTest extends TestCase
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
     * test pending tab hides permission and admin visibility posts.
     */
    public function test_pending_tab_hides_permission_and_admin_visibility_posts(): void
    {
        $author = $this->createAuthorUser(['email' => 'author-queue@example.com']);
        $moderator = $this->createModeratorUser(['email' => 'mod-queue@example.com']);
        Post::factory()->for($author)->pendingModeration()->create(['title' => 'Обычный пост']);
        Post::factory()->for($author)->pendingModeration()->withPermissionVisibility('posts.create')
            ->create(['title' => 'Permission пост']);
        Post::factory()->for($author)->pendingModeration()->create(['title' => 'Admin visibility пост',
            'visibility' => PostVisibility::Admin->value]);
        $this->actingAs($moderator)->get('/admin/posts?tab=pending')->assertOk()->assertSee('Обычный пост',
            false)->assertDontSee('Permission пост', false)->assertDontSee('Admin visibility пост', false);
    }

    /**
     * test moderator cannot approve permission visibility post.
     */
    public function test_moderator_cannot_approve_permission_visibility_post(): void
    {
        $author = $this->createAuthorUser();
        $moderator = $this->createModeratorUser();
        $post = Post::factory()->for($author)->pendingModeration()->withPermissionVisibility('posts.create')->create();
        $this->assertFalse($moderator->can('moderate', $post));
    }

    /**
     * test author can set visibility on create.
     */
    public function test_author_can_set_visibility_on_create(): void
    {
        $author = $this->createAuthorUser(['email' => 'vis-create@example.com']);
        $categoryId = Category::query()->value('id');
        $this->actingAs($author)->post(route('posts.store'), ['title' => 'Пост с visibility',
            'excerpt' => 'Краткое описание поста',
            'body' => 'Достаточно длинный текст поста.',
            'visibility' => PostVisibility::Authenticated->value, 'editor_mode' => 'simple',
            'category_id' => $categoryId])->assertRedirect();
        $post = Post::query()->where('title', 'Пост с visibility')->firstOrFail();
        $this->assertSame(PostVisibility::Authenticated->value, $post->visibility);
    }

    /**
     * test author can update visibility on rejected post.
     */
    public function test_author_can_update_visibility_on_rejected_post(): void
    {
        $author = $this->createAuthorUser(['email' => 'vis-edit@example.com']);
        $post = Post::factory()->for($author)->create(['status' => PostStatus::Rejected,
            'visibility' => PostVisibility::Guest->value]);
        $this->actingAs($author)->put(route('posts.update', $post), ['title' => $post->title, 'body' => $post->body,
            'visibility' => PostVisibility::Authenticated->value,
            'editor_mode' => 'simple'])->assertRedirect(route('posts.show', $post));
        $post->refresh();
        $this->assertSame(PostVisibility::Authenticated->value, $post->visibility);
    }

    /**
     * test re edit published post moves to pending moderation.
     */
    public function test_re_edit_published_post_moves_to_pending_moderation(): void
    {
        $author = $this->createAuthorUser(['email' => 'repub@example.com']);
        $post = Post::factory()->for($author)->published()->create(['title' => 'Был опубликован']);
        $this->actingAs($author)->put(route('posts.update', $post),
            ['title' => 'Обновлённый заголовок', 'body' => $post->body,
                'editor_mode' => 'simple'])->assertRedirect(route('posts.show', $post));
        $post->refresh();
        $this->assertSame(PostStatus::PendingModeration, $post->status);
        $this->assertFalse($post->is_published);
    }

    /**
     * test suspended user cannot access profile.
     */
    public function test_suspended_user_cannot_access_profile(): void
    {
        $user = User::factory()->suspended()->create();
        $user->assignRole('user');
        $this->actingAs($user)->get(route('profile.edit'))->assertForbidden();
    }

    /**
     * test pending user is redirected to account pending.
     */
    public function test_pending_user_is_redirected_to_account_pending(): void
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole('user');
        $this->actingAs($user)->get(route('profile.edit'))->assertRedirect(route('account.pending'));
    }

    /**
     * test staff login redirects to admin.
     */
    public function test_staff_login_redirects_to_admin(): void
    {
        $moderator = $this->createModeratorUser(['email' => 'staff-login@example.com']);
        $this->post(route('login'), ['email' => $moderator->email, 'password' => 'password'])->assertRedirect('/admin');
    }

    /**
     * test admin activate sets account status active.
     */
    public function test_admin_activate_sets_account_status_active(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'activate-status@example.com']);
        app(UserServiceContract::class)->activate($user);
        $user->refresh();
        $this->assertSame(AccountStatus::Active, $user->account_status);
        $this->assertTrue($user->hasVerifiedEmail());
    }

    /**
     * test public index does not show published badge.
     */
    public function test_public_index_does_not_show_published_badge(): void
    {
        Post::factory()->published()->create(['title' => 'Публичный пост']);
        $this->get(route('posts.index'))->assertOk()->assertDontSee(__('posts.legacy.published'), false);
    }
}
