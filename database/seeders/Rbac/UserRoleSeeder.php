<?php

namespace Database\Seeders\Rbac;

use App\Support\Rbac\RoleProvisioner;
use Database\Seeders\Rbac\Concerns\ProvisionsRole;
use Illuminate\Database\Seeder;

/**
 * Роль «user» — базовые права автора.
 */
class UserRoleSeeder extends Seeder
{
    use ProvisionsRole;

    /**
     * run.
     */
    public function run(): void
    {
        $this->syncRole('user', array_values(app(RoleProvisioner::class)->defaultUserPermissions()->all()));
    }
}
