<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Сессия и кэш браузера между админкой и публичным сайтом.
 */
class SessionPersistenceTest extends TestCase
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
     * После входа staff и перехода с /admin на публичку сессия сохраняется.
     */
    public function test_staff_session_persists_from_admin_to_public_site(): void
    {
        $moderator = $this->createModeratorUser(['email' => 'mod-session@example.com']);

        $this->post(route('login'), [
            'email' => $moderator->email,
            'password' => 'password',
        ])->assertRedirect('/admin');

        $this->get('/admin')->assertOk();
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(__('layout.nav.logout'), false);
    }

    /**
     * Авторизованный пользователь видит шапку на второй странице листинга.
     */
    public function test_authenticated_user_sees_navbar_on_listing_page_two(): void
    {
        $author = $this->createAuthorUser();
        Post::factory()->count(25)->for($author)->published()->create();

        $user = User::factory()->create(['email' => 'listing-page2@example.com']);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('posts.index', ['page' => 2]))
            ->assertOk()
            ->assertSee(__('layout.nav.logout'), false);
    }

    /**
     * После гостевого просмотра листинга вход не ломает вторую страницу.
     */
    public function test_login_after_guest_listing_page_two_shows_authenticated_navbar(): void
    {
        $author = $this->createAuthorUser();
        Post::factory()->count(25)->for($author)->published()->create();

        $this->get(route('posts.index', ['page' => 2]))->assertOk();

        $user = User::factory()->create(['email' => 'after-guest@example.com']);
        $user->assignRole('user');

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('posts.index'));

        $this->get(route('posts.index', ['page' => 2]))
            ->assertOk()
            ->assertSee(__('layout.nav.logout'), false);
    }

    /**
     * Листинг: ETag и private-кэш различаются для гостя и авторизованного.
     */
    public function test_posts_index_separates_guest_and_authenticated_cache_slots(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        Post::factory()->count(2)->for($user)->published()->create();

        $guest = $this->get(route('posts.index'));
        $guest->assertOk();
        $guest->assertHeader('Vary', 'Cookie');
        $guestCacheControl = (string) $guest->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $guestCacheControl);
        $this->assertStringNotContainsString('no-store', $guestCacheControl);

        $auth = $this->actingAs($user)->get(route('posts.index'));
        $auth->assertOk();
        $auth->assertHeader('Vary', 'Cookie');
        $authCacheControl = (string) $auth->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $authCacheControl);

        $guestEtag = $guest->headers->get('ETag');
        $authEtag = $auth->headers->get('ETag');
        $this->assertNotNull($guestEtag);
        $this->assertNotNull($authEtag);
        $this->assertNotSame($guestEtag, $authEtag);
    }

    /**
     * Главная страница получает private + Vary: Cookie без conditional.get.
     */
    public function test_home_page_has_audience_cache_headers(): void
    {
        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertHeader('Vary', 'Cookie');

        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('max-age=', $cacheControl);
    }
}
