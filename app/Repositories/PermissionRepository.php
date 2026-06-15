<?php

namespace App\Repositories;

use App\Repositories\Contracts\PermissionRepositoryContract;
use App\Support\TypeCast;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Репозиторий прав — единственная точка записи в permissions.
 *
 * @property-read Permission $permission
 * @property-read PermissionRegistrar $permissionRegistrar
 */
class PermissionRepository implements PermissionRepositoryContract
{
    public function __construct(protected Permission $permission, protected PermissionRegistrar $permissionRegistrar) {}

    /**
     * {@inheritdoc}
     */
    public function findOrCreate(string $name, string $guard = 'web'): Permission
    {
        $permission = Permission::findOrCreate($name, $guard);
        if (! $permission instanceof Permission) {
            throw new \RuntimeException('Expected Spatie Permission instance.');
        }

        return $permission;
    }

    /**
     * {@inheritdoc}
     */
    public function ensureExist(Collection $permissionNames, string $guard = 'web'): void
    {
        foreach ($permissionNames as $name) {
            $this->findOrCreate($name, $guard);
        }
        $this->permissionRegistrar->forgetCachedPermissions();
    }

    /**
     * {@inheritdoc}
     */
    public function allPermissionNames(string $guard = 'web'): Collection
    {
        return $this->permission->newQuery()->where('guard_name', $guard)->pluck('name')
            ->map(fn (mixed $name): string => TypeCast::string($name))->values();
    }
}
