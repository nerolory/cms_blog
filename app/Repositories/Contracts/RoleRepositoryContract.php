<?php

namespace App\Repositories\Contracts;

use App\Models\Role;
use Illuminate\Support\Collection;

/**
 * Контракт репозитория ролей Spatie Permission.
 */
interface RoleRepositoryContract
{
    /**
     * count.

     *
     * @return int
     */
    public function count(): int;

    /**
     * Находит роль или создаёт её в guard.
     *
     * @return Role
     */
    public function findOrCreate(string $name, string $guard = 'web'): Role;

    /**
     * Синхронизирует права роли.
     *
     * @param  Collection<int, string>  $permissionNames
     * @return Role
     */
    public function syncPermissions(Role $role, Collection $permissionNames): Role;
}
