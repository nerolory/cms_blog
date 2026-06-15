<?php

namespace Database\Seeders\Rbac;

use Database\Seeders\Rbac\Concerns\ProvisionsRole;
use Illuminate\Database\Seeder;

/**
 * Роль «owner» — все permissions.
 */
class OwnerRoleSeeder extends Seeder
{
    use ProvisionsRole;

    /**
     * run.
     */
    public function run(): void
    {
        $this->syncRoleWithAllPermissions('owner');
    }
}
