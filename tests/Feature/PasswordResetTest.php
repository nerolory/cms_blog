<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс password reset.
 */
class PasswordResetTest extends TestCase
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
     * test guest can view forgot password page.
     */
    public function test_guest_can_view_forgot_password_page(): void
    {
        $this->get(route('password.request'))->assertOk();
    }

    /**
     * test forgot password sends notification.
     */
    public function test_forgot_password_sends_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'reset@example.com']);
        $user->assignRole('user');
        $this->post(route('password.email'), ['email' => 'reset@example.com'])->assertSessionHasNoErrors();
        Notification::assertSentTo($user, ResetPassword::class);
    }
}
