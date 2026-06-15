<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\User;
use App\Repositories\Contracts\MailSettingsRepositoryContract;
use App\Services\Contracts\UserServiceContract;
use App\Support\Mail\MailSettingKey;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс email verification.
 */
class EmailVerificationTest extends TestCase
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
     * test registration with verification enabled sends email and redirects to notice.
     */
    public function test_registration_with_verification_enabled_sends_email_and_redirects_to_notice(): void
    {
        Notification::fake();
        app(MailSettingsRepositoryContract::class)->set(MailSettingKey::REQUIRE_EMAIL_VERIFICATION, '1');
        $response = $this->post(route('register'), ['name' => 'Verify User', 'email' => 'verify@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123']);
        $response->assertRedirect(route('verification.notice'));
        $user = User::query()->where('email', 'verify@example.com')->first();
        $this->assertInstanceOf(User::class, $user);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * test unverified user cannot access profile when verification enabled.
     */
    public function test_unverified_user_cannot_access_profile_when_verification_enabled(): void
    {
        app(MailSettingsRepositoryContract::class)->set(MailSettingKey::REQUIRE_EMAIL_VERIFICATION, '1');
        $user = User::factory()->unverified()->create();
        $user->assignRole('user');
        $this->actingAs($user)->get(route('profile.edit'))->assertRedirect(route('verification.notice'));
    }

    /**
     * test admin can activate user.
     */
    public function test_admin_can_activate_user(): void
    {
        $this->seedRoles();
        $user = User::factory()->unverified()->create(['email' => 'inactive@example.com']);
        app(UserServiceContract::class)->activate($user);
        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertSame(AccountStatus::Active, $user->account_status);
    }

    /**
     * test unverified user without verification setting is redirected to pending.
     */
    public function test_unverified_user_without_verification_setting_is_redirected_to_pending(): void
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole('user');
        $this->actingAs($user)->get(route('profile.edit'))->assertRedirect(route('account.pending'));
    }
}
