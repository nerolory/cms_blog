<?php

namespace App\Repositories;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryContract;
use Illuminate\Support\Collection;

/**
 * Репозиторий ролей — единственная точка подсчёта и записи roles.
 *
 * @property-read Role $role
 */
class RoleRepository implements RoleRepositoryContract
{
    public function __construct(protected Role $role) {}

    /**
     * count.

     *
     * @return int
     */
    public function count(): int
    {
        return $this->role->newQuery()->count();
    }

    /**
     * {@inheritdoc}
     */
    public function findOrCreate(string $name, string $guard = 'web'): Role
    {
        $role = Role::findOrCreate($name, $guard);
        if (! $role instanceof Role) {
            throw new \RuntimeException('Expected App\Models\Role instance.');
        }

        return $role;
    }

    /**
     * {@inheritdoc}
     */
    public function syncPermissions(Role $role, Collection $permissionNames): Role
    {
        /** @var list<string> $names */
        $names = $permissionNames->values()->all();
        $role->syncPermissions($names);

        return $role;
    }
}
