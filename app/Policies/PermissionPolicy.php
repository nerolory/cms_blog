<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Permission\Models\Permission;

/**
 * Политика доступа permission policy.
 */
class PermissionPolicy
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
        return $authUser->can('ViewAny:Permission');
    }

    /**
     * view.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function view(AuthUser $authUser, Permission $permission): bool
    {
        return $authUser->can('View:Permission');
    }

    /**
     * Создаёт .
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Permission');
    }

    /**
     * Обновляет .
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function update(AuthUser $authUser, Permission $permission): bool
    {
        return $authUser->can('Update:Permission');
    }

    /**
     * Удаляет .
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function delete(AuthUser $authUser, Permission $permission): bool
    {
        return $authUser->can('Delete:Permission');
    }

    /**
     * Удаляет any.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Permission');
    }

    /**
     * restore.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function restore(AuthUser $authUser, Permission $permission): bool
    {
        return $authUser->can('Restore:Permission');
    }

    /**
     * force delete.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function forceDelete(AuthUser $authUser, Permission $permission): bool
    {
        return $authUser->can('ForceDelete:Permission');
    }

    /**
     * force delete any.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Permission');
    }

    /**
     * restore any.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Permission');
    }

    /**
     * replicate.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function replicate(AuthUser $authUser, Permission $permission): bool
    {
        return $authUser->can('Replicate:Permission');
    }

    /**
     * reorder.
     *
     * @param  AuthUser  $authUser  user

     * @return bool
     */
    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Permission');
    }
}
