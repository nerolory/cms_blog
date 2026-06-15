<?php

namespace Database\Seeders\Rbac\Concerns;

use App\Repositories\Contracts\PermissionRepositoryContract;
use App\Support\Rbac\RoleProvisioner;

/**
 * Сидер БД provisions role.
 */
trait ProvisionsRole
{
    /**
     * sync role.
     *
     * @param  list<string>  $permissionNames
     */
    protected function syncRole(string $name, array $permissionNames): void
    {
        app(RoleProvisioner::class)->syncRole($name, collect($permissionNames));
    }

    /**
     * sync role with all permissions.
     */
    protected function syncRoleWithAllPermissions(string $name): void
    {
        $permissions = app(PermissionRepositoryContract::class)->allPermissionNames();
        app(RoleProvisioner::class)->syncRole($name, $permissions);
    }
}
