<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс post controller.
 */
class PostControllerTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    private const VALID_BODY =
        'Достаточно длинный текст тела поста для валидации.';

    /**
     * Подготавливает окружение теста.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        Cache::flush();
    }

    /**
     * Index action displays the posts listing view.
     */
    public function test_index_displays_posts_view(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(3)->for($user)->published()->create();
        $response = $this->get(route('posts.index'));
        $response->assertOk()->assertViewIs('pages.posts.index')->assertViewHas('posts');
    }

    /**
     * Store action creates a post and redirects.
     */
    public function test_store_creates_post_and_redirects(): void
    {
        $user = $this->createAuthorUser();
        $postData = ['title' => 'Novyi unikalnyi zagolovok posta',
            'excerpt' => 'Kratkoe prevyu dlia etogo posta, prevyshaiushchee desiat simvolov',
            'body' => 'Polnoe telo posta, soderzhashchee dlinnyi i soderzhatelnyi tekst', 'visibility' => 'guest',
            'is_published' => true, 'category_id' => Category::query()->value('id'), 'editor_mode' => 'simple'];
        $response = $this->actingAs($user)->post(route('posts.store'), $postData);
        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('posts', ['slug' => 'novyi-unikalnyi-zagolovok-posta',
            'title' => 'Novyi unikalnyi zagolovok posta', 'status' => PostStatus::PendingModeration->value,
            'user_id' => $user->id]);
        $post = Post::query()->where('slug', 'novyi-unikalnyi-zagolovok-posta')->firstOrFail();
        $response->assertRedirectToRoute('posts.show', $post);
    }

    /**
     * Show action displays the post detail view.
     */
    public function test_show_displays_posts_show_view(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->published()->create();
        $response = $this->get(route('posts.show', $post));
        $response->assertOk()->assertViewIs('pages.posts.show')->assertViewHas('post');
    }

    /**
     * Update action modifies the post and redirects with success.
     */
    public function test_update_modifies_post_and_redirects_with_success(): void
    {
        $user = $this->createAuthorUser();
        $post = Post::factory()->for($user)->pendingModeration()
            ->create(['title' => 'Старый заголовок']);
        $updatedData = [
            'title' => 'Новый заголовок',
            'slug' => $post->slug,
            'excerpt' => 'Новое превью поста, превышающее десять символов',
            'body' => self::VALID_BODY,
            'visibility' => 'guest',
            'is_published' => false,
        ];
        $response = $this->actingAs($user)->put(route('posts.update', $post), $updatedData);
        $response->assertSessionHasNoErrors()->assertRedirectToRoute('posts.show', $post)->assertSessionHas('success',
            __('posts.messages.updated'));
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Новый заголовок']);
    }

    /**
     * test update keeps slug and excerpt when blank optional fields submitted.
     */
    public function test_update_keeps_slug_and_excerpt_when_blank_optional_fields_submitted(): void
    {
        $user = $this->createAuthorUser();
        $post = Post::factory()->for($user)->pendingModeration()->create(['slug' => 'original-slug',
            'excerpt' => 'Исходное превью поста для проверки сохранения']);
        $this->actingAs($user)->put(route('posts.update', $post), [
            'title' => 'Обновлённый заголовок',
            'slug' => '',
            'excerpt' => '   ',
            'body' => self::VALID_BODY,
            'visibility' => 'guest',
            'is_published' => false,
        ])->assertSessionHasNoErrors()->assertRedirectToRoute('posts.show', $post);
        $post->refresh();
        $this->assertSame('original-slug', $post->slug);
        $this->assertSame('Исходное превью поста для проверки сохранения',
            $post->excerpt);
        $this->assertSame('Обновлённый заголовок', $post->title);
    }

    /**
     * Update action redirects back with validation errors on invalid data.
     */
    public function test_update_redirects_back_with_errors_on_invalid_data(): void
    {
        $user = $this->createAuthorUser();
        $post = Post::factory()->for($user)->pendingModeration()->create();
        $invalidData = ['title' => '', 'slug' => $post->slug, 'body' => 'Текст'];
        $response = $this->actingAs($user)->from(route('posts.show', $post))->put(route('posts.update', $post),
            $invalidData);
        $response->assertRedirect(route('posts.show', $post))->assertInvalid(['title']);
    }

    /**
     * test post update is limited to ten requests per minute.
     */
    public function test_post_update_is_limited_to_ten_requests_per_minute(): void
    {
        $user = $this->createAuthorUser();
        $post = Post::factory()->for($user)->pendingModeration()
            ->create(['excerpt' => 'Исходное превью поста для проверки лимита']);
        $payload = [
            'title' => 'Заголовок для проверки лимита сохранений',
            'slug' => $post->slug,
            'excerpt' => 'Исходное превью поста для проверки лимита',
            'body' => self::VALID_BODY,
            'visibility' => 'guest',
            'is_published' => false,
        ];
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->from(route('posts.edit', $post))->put(route('posts.update', $post),
                $payload)->assertRedirect();
        }
        $this->actingAs($user)->from(route('posts.edit', $post))->put(route('posts.update', $post), $payload)
            ->assertRedirect(route('posts.edit', $post))->assertSessionHasErrors('form_error');
    }

    /**
     * test post store is limited to ten attempts per minute.
     */
    public function test_post_store_is_limited_to_ten_attempts_per_minute(): void
    {
        $user = $this->createAuthorUser();
        $invalidPayload = ['title' => '', 'excerpt' => 'short', 'body' => 'short', 'visibility' => 'guest',
            'editor_mode' => 'simple'];
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->post(route('posts.store'), $invalidPayload)->assertSessionHasErrors();
        }
        $this->actingAs($user)->post(route('posts.store'), $invalidPayload)->assertStatus(429);
    }

    /**
     * test post destroy is limited to ten attempts per minute.
     */
    public function test_post_destroy_is_limited_to_ten_attempts_per_minute(): void
    {
        $user = $this->createAuthorUser();
        for ($i = 0; $i < 10; $i++) {
            $post = Post::factory()->for($user)->pendingModeration()->create();
            $this->actingAs($user)->delete(route('posts.destroy', $post))->assertRedirect();
        }
        $post = Post::factory()->for($user)->pendingModeration()->create();
        $this->actingAs($user)->delete(route('posts.destroy', $post))->assertStatus(429);
    }

    /**
     * test edit form disables update button until changes.
     */
    public function test_edit_form_disables_update_button_until_changes(): void
    {
        $user = $this->createAuthorUser();
        $post = Post::factory()->for($user)->pendingModeration()->create();
        $this->actingAs($user)->get(route('posts.edit', $post))->assertOk()->assertSee('id="post-edit-form"',
            false)->assertSee('id="post-update-btn"', false)->assertSee('disabled', false);
    }

    /**
     * Destroy action soft-deletes the post and redirects with success.
     */
    public function test_destroy_soft_deletes_post_and_redirects_with_success(): void
    {
        $user = $this->createAuthorUser();
        $post = Post::factory()->for($user)->pendingModeration()->create();
        $response = $this->actingAs($user)->delete(route('posts.destroy', $post));
        $response->assertRedirectToRoute('posts.index')->assertSessionHas('success', __('posts.messages.deleted'));
        $this->assertSoftDeleted($post);
    }

    /**
     * Guest cannot create a post without authentication.
     */
    public function test_guest_cannot_store_post(): void
    {
        $response = $this->post(route('posts.store'), ['title' => 'Заголовок', 'slug' => 'zagolovok',
            'excerpt' => 'Краткое превью поста',
            'body' => 'Текст поста достаточной длины']);
        $response->assertRedirect(route('login'));
    }

    /**
     * Authenticated author can open the create form.
     */
    public function test_create_displays_form_for_author(): void
    {
        $user = $this->createAuthorUser();
        $this->actingAs($user)->get(route('posts.create'))->assertOk()->assertViewIs('pages.posts.create');
    }

    /**
     * test author sees edit button on own pending post show page.
     */
    public function test_author_sees_edit_button_on_own_pending_post_show_page(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->pendingModeration()->create();
        $this->actingAs($author)->get(route('posts.show', $post))->assertOk()->assertSee(route('posts.edit', $post,
            absolute: false), false)->assertSee(__('posts.web.edit'));
    }

    /**
     * test author sees edit button on own published post show page.
     */
    public function test_author_sees_edit_button_on_own_published_post_show_page(): void
    {
        $author = $this->createAuthorUser();
        $post = Post::factory()->for($author)->published()->create();
        $this->actingAs($author)->get(route('posts.show', $post))->assertOk()->assertSee(route('posts.edit', $post,
            absolute: false), false)->assertSee(__('posts.web.edit'));
    }

    /**
     * test moderator sees edit and admin buttons on post show page.
     */
    public function test_moderator_sees_edit_and_admin_buttons_on_post_show_page(): void
    {
        $author = $this->createAuthorUser(['email' => 'author-show-actions@example.com']);
        $moderator = $this->createModeratorUser(['email' => 'moderator-show-actions@example.com']);
        $post = Post::factory()->for($author)->pendingModeration()->create();
        $this->actingAs($moderator)->get(route('posts.show', $post))->assertOk()->assertSee(route('posts.edit', $post,
            absolute: false), false)->assertSee(__('posts.web.open_in_admin'));
    }

    /**
     * test owner sees edit and admin buttons on published post show page.
     */
    public function test_owner_sees_edit_and_admin_buttons_on_published_post_show_page(): void
    {
        $author = $this->createAuthorUser(['email' => 'author-owner-actions@example.com']);
        $owner = $this->createOwnerUser(['email' => 'owner-show-actions@example.com']);
        $post = Post::factory()->for($author)->published()->create();
        $this->actingAs($owner)->get(route('posts.show', $post))->assertOk()->assertSee(route('posts.edit', $post,
            absolute: false), false)->assertSee(__('posts.web.open_in_admin'));
    }

    /**
     * test owner edit form lists users in author select.
     */
    public function test_owner_edit_form_lists_users_in_author_select(): void
    {
        $author = $this->createAuthorUser(['email' => 'author-select@example.com', 'name' => 'Author Select']);
        $owner = $this->createOwnerUser(['email' => 'owner-select@example.com']);
        $post = Post::factory()->for($author)->published()->create();
        $this->actingAs($owner)->get(route('posts.edit',
            $post))->assertOk()->assertSee('Author Select (author-select@example.com)',
                false)->assertSee('value="'.$author->id.'"', false)->assertSee('data-author-select="true"', false);
    }

    /**
     * test author cannot reassign post owner via forged user id.
     */
    public function test_author_cannot_reassign_post_owner_via_forged_user_id(): void
    {
        $author = $this->createAuthorUser(['email' => 'author-idor@example.com']);
        $intruder = $this->createAuthorUser(['email' => 'intruder-idor@example.com']);
        $post = Post::factory()->for($author)->pendingModeration()
            ->create(['title' => 'Исходный заголовок']);
        assert($post instanceof Post);
        $payload = [
            'title' => 'Обновлённый заголовок',
            'slug' => $post->slug,
            'excerpt' => 'Превью поста для проверки IDOR автора',
            'body' => self::VALID_BODY,
            'visibility' => 'guest',
            'is_published' => false,
            'user_id' => $intruder->id,
        ];
        $this->actingAs($author)->put(route('posts.update', $post), $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'user_id' => $author->id,
            'title' => 'Обновлённый заголовок']);
    }

    /**
     * test guest does not see post action buttons.
     */
    public function test_guest_does_not_see_post_action_buttons(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->published()->create();
        $this->get(route('posts.show', $post))->assertOk()->assertDontSee(route('posts.edit', $post, absolute: false),
            false)->assertDontSee(__('posts.web.open_in_admin'));
    }
}
