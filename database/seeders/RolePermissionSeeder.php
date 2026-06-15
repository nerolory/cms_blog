<?php

namespace Database\Seeders;

use Database\Seeders\Rbac\AdminRoleSeeder;
use Database\Seeders\Rbac\ModeratorRoleSeeder;
use Database\Seeders\Rbac\OwnerRoleSeeder;
use Database\Seeders\Rbac\OwnerUserSeeder;
use Database\Seeders\Rbac\PermissionSeeder;
use Database\Seeders\Rbac\ShieldPermissionsSeeder;
use Database\Seeders\Rbac\UserRoleSeeder;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Оркестратор RBAC: permissions → роли → owner.
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * run.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->call([PermissionSeeder::class, ShieldPermissionsSeeder::class, UserRoleSeeder::class,
            ModeratorRoleSeeder::class, AdminRoleSeeder::class, OwnerRoleSeeder::class, OwnerUserSeeder::class]);
    }
}
