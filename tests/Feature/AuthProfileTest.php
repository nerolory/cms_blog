<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс auth profile.
 */
class AuthProfileTest extends TestCase
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
     * test guest can view login and register pages.
     */
    public function test_guest_can_view_login_and_register_pages(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
    }

    /**
     * test user can register and is logged in.
     */
    public function test_user_can_register_and_is_logged_in(): void
    {
        $response = $this->post(route('register'), ['name' => 'Новый пользователь',
            'email' => 'new@example.com', 'password' => 'password123', 'password_confirmation' => 'password123']);
        $response->assertRedirect(route('account.pending'));
        $registeredUser = User::query()->where('email', 'new@example.com')->first();
        $this->assertInstanceOf(User::class, $registeredUser);
        $this->assertAuthenticatedAs($registeredUser);
        $this->assertNull($registeredUser->email_verified_at);
    }

    /**
     * test user can login and logout.
     */
    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['email' => 'login@example.com', 'password' => 'password123']);
        $this->post(route('login'), ['email' => 'login@example.com',
            'password' => 'password123'])->assertRedirect(route('posts.index'));
        $this->assertAuthenticatedAs($user);
        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    /**
     * test authenticated user can update profile and theme.
     */
    public function test_authenticated_user_can_update_profile_and_theme(): void
    {
        $user = User::factory()->create(['theme' => 'default', 'email' => 'profile@example.com',
            'password' => 'password123']);
        $this->actingAs($user)->patch(route('profile.update'), ['name' => 'Обновлённое имя',
            'email' => 'updated@example.com', 'password' => '', 'password_confirmation' => '',
            'theme' => 'dark'])->assertRedirect(route('profile.edit'))->assertSessionHas('success',
                __('profile.messages.updated'));
        $user->refresh();
        $this->assertSame('Обновлённое имя', $user->name);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertSame('dark', $user->theme);
        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->post(route('login'), ['email' => 'updated@example.com',
            'password' => 'password123'])->assertRedirect(route('posts.index'));
    }

    /**
     * test profile update with whitespace password does not change password.
     */
    public function test_profile_update_with_whitespace_password_does_not_change_password(): void
    {
        $user = User::factory()->create(['email' => 'spaces@example.com', 'password' => 'password123',
            'theme' => 'default']);
        $this->actingAs($user)->from(route('profile.edit'))->patch(route('profile.update'), ['name' => $user->name,
            'email' => $user->email, 'password' => '   ', 'password_confirmation' => '   ',
            'theme' => 'default'])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));
        $this->post(route('logout'));
        $this->post(route('login'), ['email' => 'spaces@example.com',
            'password' => 'password123'])->assertRedirect(route('posts.index'));
    }

    /**
     * test authenticated user can upload and delete avatar.
     */
    public function test_authenticated_user_can_upload_and_delete_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('profile.avatar.store'),
            ['avatar' => UploadedFile::fake()->image('avatar.jpg', 200, 200)])->assertRedirect(route('profile.edit'));
        $user->refresh();
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);
        $this->actingAs($user)->delete(route('profile.avatar.destroy'))->assertRedirect(route('profile.edit'));
        $user->refresh();
        $this->assertNull($user->avatar_path);
    }
}
