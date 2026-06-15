<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Политика доступа role policy.
 */
class RolePolicy
{
    use HandlesAuthorization;

    /**
     * view any.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Role');
    }

    /**
     * view.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function view(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('View:Role');
    }

    /**
     * Создаёт .
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Role');
    }

    /**
     * Обновляет .
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function update(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('Update:Role');
    }

    /**
     * Удаляет .
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function delete(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('Delete:Role');
    }

    /**
     * Удаляет any.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Role');
    }

    /**
     * restore.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function restore(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('Restore:Role');
    }

    /**
     * force delete.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function forceDelete(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('ForceDelete:Role');
    }

    /**
     * force delete any.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Role');
    }

    /**
     * restore any.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Role');
    }

    /**
     * replicate.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function replicate(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('Replicate:Role');
    }

    /**
     * reorder.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Role');
    }
}
