<?php

namespace Database\Seeders\Rbac;

use App\Support\TypeCast;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

/**
 * Registers permissions from config/permissions.php.
 */
class PermissionSeeder extends Seeder
{
    /**
     * run.
     */
    public function run(): void
    {
        foreach (TypeCast::array(config('permissions', [])) as $name) {
            Permission::findOrCreate(TypeCast::string($name), 'web');
        }
    }
}
