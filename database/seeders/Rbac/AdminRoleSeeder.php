<?php

namespace Database\Seeders\Rbac;

use Database\Seeders\Rbac\Concerns\ProvisionsRole;
use Illuminate\Database\Seeder;

/**
 * Роль «admin» — все permissions.
 */
class AdminRoleSeeder extends Seeder
{
    use ProvisionsRole;

    /**
     * run.
     */
    public function run(): void
    {
        $this->syncRoleWithAllPermissions('admin');
    }
}
