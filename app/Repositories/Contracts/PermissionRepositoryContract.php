<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

/**
 * Контракт репозитория прав Spatie Permission.
 */
interface PermissionRepositoryContract
{
    /**
     * Находит право или создаёт его в guard.
     *
     * @return Permission
     */
    public function findOrCreate(string $name, string $guard = 'web'): Permission;

    /**
     * Гарантирует наличие прав и сбрасывает кэш Spatie.
     *
     * @param  Collection<int, string>  $permissionNames
     */
    public function ensureExist(Collection $permissionNames, string $guard = 'web'): void;

    /**
     * Имена всех прав guard (для синхронизации ролей в сидерах).
     *
     * @return Collection<int, string>
     */
    public function allPermissionNames(string $guard = 'web'): Collection;
}
