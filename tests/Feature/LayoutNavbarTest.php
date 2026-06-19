<?php

namespace Tests\Feature;

use App\DTO\TokenGrantData;
use App\Models\User;
use App\Services\Contracts\TokenWalletServiceContract;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Проверка навигации в шапке сайта.
 */
class LayoutNavbarTest extends TestCase
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
     * Авторизованному пользователю в шапке показывается баланс
     * токенов.
     */
    public function test_navbar_shows_token_balance_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        app(TokenWalletServiceContract::class)->grant(new TokenGrantData(
            userId: $user->id,
            amount: 42,
            grantedByUserId: $user->id,
            note: 'test',
        ));

        $this->actingAs($user)
            ->get(route('posts.index'))
            ->assertOk()
            ->assertSee('class="btn btn-secondary btn-sm layout-navbar__balance"', false)
            ->assertSee(__('layout.nav.tokens_balance', ['balance' => '42']), false)
            ->assertSee(route('tokens.show'), false);
    }

    /**
     * Большой баланс в шапке сокращается до K/M/B.
     */
    public function test_navbar_shows_compact_token_balance_for_large_amounts(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        app(TokenWalletServiceContract::class)->grant(new TokenGrantData(
            userId: $user->id,
            amount: 150_000,
            grantedByUserId: $user->id,
            note: 'test',
        ));

        $this->actingAs($user)
            ->get(route('posts.index'))
            ->assertOk()
            ->assertSee(__('layout.nav.tokens_balance', ['balance' => '150K']), false);
    }

    /**
     * Модератор видит ссылку на админку в шапке публичного сайта.
     */
    public function test_moderator_sees_admin_link_in_navbar(): void
    {
        $moderator = $this->createModeratorUser(['email' => 'mod-navbar@example.com']);

        $this->actingAs($moderator)
            ->get(route('posts.index'))
            ->assertOk()
            ->assertSee(__('layout.nav.admin'), false)
            ->assertSee('class="btn btn-outline-primary btn-sm layout-navbar__admin"', false)
            ->assertSee('/admin', false);
    }

    /**
     * Обычный пользователь не видит ссылку на админку в шапке.
     */
    public function test_regular_user_does_not_see_admin_link_in_navbar(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('posts.index'))
            ->assertOk()
            ->assertDontSee(__('layout.nav.admin'), false);
    }

    /**
     * Повторный запрос /posts не падает у авторизованного
     * пользователя.
     */
    public function test_posts_index_second_request_for_authenticated_user(): void
    {
        $moderator = $this->createModeratorUser(['email' => 'mod-nav-cache@example.com']);
        $this->actingAs($moderator)->get(route('posts.index'))->assertOk();
        $this->actingAs($moderator)->get(route('posts.index'))->assertOk();
    }
}
