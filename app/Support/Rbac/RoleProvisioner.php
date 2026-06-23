<?php

namespace App\Support\Rbac;

use App\Models\Role;
use App\Repositories\Contracts\PermissionRepositoryContract;
use App\Repositories\Contracts\RoleRepositoryContract;
use Illuminate\Support\Collection;

/**
 * Оркестрация RBAC: какие права у ролей, без прямого доступа к БД.
 *
 * @property-read RoleRepositoryContract $roleRepository
 * @property-read PermissionRepositoryContract $permissionRepository
 */
class RoleProvisioner
{
    public function __construct(protected RoleRepositoryContract $roleRepository,
        protected PermissionRepositoryContract $permissionRepository) {}

    /**
     * Возвращает права роли user по умолчанию.
     *
     * @return Collection<int, string>
     */
    public function defaultUserPermissions(): Collection
    {
        return collect([
            'posts.create',
            'posts.update.own',
            'posts.delete.own',
            'ai.orders.request',
            'tokens.purchase',
        ]);
    }

    /**
     * Синхронизирует роль и набор прав.
     *
     * @param  Collection<int, string>  $permissionNames
     * @return Role
     */
    public function syncRole(string $name, Collection $permissionNames): Role
    {
        $this->permissionRepository->ensureExist($permissionNames);
        $role = $this->roleRepository->findOrCreate($name);

        return $this->roleRepository->syncPermissions($role, $permissionNames);
    }

    /**
     * Гарантирует наличие роли user с правами по умолчанию.
     *
     * @return Role
     */
    public function ensureUserRole(): Role
    {
        return $this->syncRole('user', $this->defaultUserPermissions());
    }
}
