<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Проверка rate limit на auth POST-маршрутах (TD-SEC-03, H1).
 */
class AuthThrottleTest extends TestCase
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
     * test login post is limited to five attempts per minute.
     */
    public function test_login_post_is_limited_to_five_attempts_per_minute(): void
    {
        User::factory()->create(['email' => 'throttle-login@example.com', 'password' => 'password123']);
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), ['email' => 'throttle-login@example.com',
                'password' => 'wrong-password'])->assertRedirect();
        }
        $this->post(route('login'), ['email' => 'throttle-login@example.com',
            'password' => 'wrong-password'])->assertStatus(429);
    }

    /**
     * test register post is limited to five attempts per minute.
     */
    public function test_register_post_is_limited_to_five_attempts_per_minute(): void
    {
        $invalidPayload = ['name' => '', 'email' => 'not-an-email', 'password' => 'short',
            'password_confirmation' => 'x'];
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('register'), $invalidPayload)->assertSessionHasErrors();
        }
        $this->post(route('register'), $invalidPayload)->assertStatus(429);
    }

    /**
     * test forgot password post is limited to five attempts per minute.
     */
    public function test_forgot_password_post_is_limited_to_five_attempts_per_minute(): void
    {
        User::factory()->create(['email' => 'forgot-throttle@example.com']);
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('password.email'), ['email' => 'forgot-throttle@example.com'])->assertRedirect();
        }
        $this->post(route('password.email'), ['email' => 'forgot-throttle@example.com'])->assertStatus(429);
    }

    /**
     * test reset password post is limited to five attempts per minute.
     */
    public function test_reset_password_post_is_limited_to_five_attempts_per_minute(): void
    {
        $invalidPayload = ['token' => 'invalid-token', 'email' => 'not-an-email', 'password' => 'short',
            'password_confirmation' => 'x'];
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('password.update'), $invalidPayload)->assertSessionHasErrors();
        }
        $this->post(route('password.update'), $invalidPayload)->assertStatus(429);
    }

    /**
     * test profile update is limited to ten attempts per minute.
     */
    public function test_profile_update_is_limited_to_ten_attempts_per_minute(): void
    {
        $user = User::factory()->create(['email' => 'profile-throttle@example.com']);
        $invalidPayload = ['name' => '', 'email' => 'not-an-email', 'theme' => 'default'];
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->patch(route('profile.update'), $invalidPayload)->assertSessionHasErrors();
        }
        $this->actingAs($user)->patch(route('profile.update'), $invalidPayload)->assertStatus(429);
    }
}
