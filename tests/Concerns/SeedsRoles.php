<?php

namespace Tests\Concerns;

use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\MailSettingsSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SiteTemplateSeeder;

/**
 * Seeds RBAC for feature tests.
 */
trait SeedsRoles
{
    /**
     * seed roles.
     */
    protected function seedRoles(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(MailSettingsSeeder::class);
        $this->seed(SiteTemplateSeeder::class);
        $this->seed(CategorySeeder::class);
    }

    /**
     * Создаёт author user.
     *
     * @param  array<string, mixed>  $attributes
     * @return User
     */
    protected function createAuthorUser(array $attributes = []): User
    {
        $this->seedRoles();
        /** @var User $user */
        $user = User::factory()->create($attributes);
        $user->assignRole('user');
        $freshUser = $user->fresh();
        if (! $freshUser instanceof User) {
            throw new \RuntimeException('Failed to refresh author user.');
        }

        return $freshUser;
    }

    /**
     * Создаёт moderator user.
     *
     * @param  array<string, mixed>  $attributes
     * @return User
     */
    protected function createModeratorUser(array $attributes = []): User
    {
        $this->seedRoles();
        /** @var User $user */
        $user = User::factory()->create($attributes);
        $user->assignRole('moderator');
        $freshUser = $user->fresh();
        if (! $freshUser instanceof User) {
            throw new \RuntimeException('Failed to refresh moderator user.');
        }

        return $freshUser;
    }

    /**
     * Создаёт owner user.
     *
     * @param  array<string, mixed>  $attributes
     * @return User
     */
    protected function createOwnerUser(array $attributes = []): User
    {
        $this->seedRoles();
        /** @var User $user */
        $user = User::factory()->create($attributes);
        $user->assignRole('owner');
        $freshUser = $user->fresh();
        if (! $freshUser instanceof User) {
            throw new \RuntimeException('Failed to refresh owner user.');
        }

        return $freshUser;
    }
}
