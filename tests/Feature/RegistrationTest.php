<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

/**
 * Класс registration.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * test registration provisions user role without rbac seed.
     */
    public function test_registration_provisions_user_role_without_rbac_seed(): void
    {
        $response = $this->post(route('register'), ['name' => 'Fresh User', 'email' => 'fresh@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123']);
        $response->assertRedirect(route('account.pending'));
        $user = User::query()->where('email', 'fresh@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('user'));
        $this->assertTrue($user->can('posts.create'));
    }
}
