<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Rbac\DefaultUserRoleAssigner;
use Database\Seeders\RolePermissionSeeder;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

/**
 * Роль user по умолчанию для новых учётных записей.
 */
class UserDefaultRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_user_receives_user_role_by_default(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();

        $this->assertTrue($user->fresh()?->hasRole('user'));
    }

    public function test_assigner_does_not_override_existing_roles(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->syncRoles(['moderator']);

        app(DefaultUserRoleAssigner::class)->assignIfMissing($user->fresh() ?? $user);

        $this->assertTrue($user->fresh()?->hasRole('moderator'));
        $this->assertFalse($user->fresh()?->hasRole('user'));
    }
}
