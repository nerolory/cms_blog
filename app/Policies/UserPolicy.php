<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Политика доступа user policy.
 */
class UserPolicy
{
    use HandlesAuthorization;

    /**
     * view any.
     *
     * @param  User  $authUser  user

     * @return bool
     */
    public function viewAny(User $authUser): bool
    {
        return $authUser->can('ViewAny:User');
    }

    /**
     * view.
     *
     * @param  User  $authUser  user
     * @param  User  $user  пользователь

     * @return bool
     */
    public function view(User $authUser, User $user): bool
    {
        return $authUser->can('View:User');
    }

    /**
     * Создаёт .
     *
     * @param  User  $authUser  user

     * @return bool
     */
    public function create(User $authUser): bool
    {
        return $authUser->can('Create:User');
    }

    /**
     * Обновляет .
     *
     * @param  User  $authUser  user
     * @param  User  $user  пользователь

     * @return bool
     */
    public function update(User $authUser, User $user): bool
    {
        return $authUser->can('Update:User');
    }

    /**
     * Удаляет .
     *
     * @param  User  $authUser  user
     * @param  User  $user  пользователь

     * @return bool
     */
    public function delete(User $authUser, User $user): bool
    {
        return $authUser->can('Delete:User');
    }

    /**
     * Удаляет any.
     *
     * @param  User  $authUser  user

     * @return bool
     */
    public function deleteAny(User $authUser): bool
    {
        return $authUser->can('DeleteAny:User');
    }

    /**
     * restore.
     *
     * @param  User  $authUser  user
     * @param  User  $user  пользователь

     * @return bool
     */
    public function restore(User $authUser, User $user): bool
    {
        return $authUser->can('Restore:User');
    }

    /**
     * force delete.
     *
     * @param  User  $authUser  user
     * @param  User  $user  пользователь

     * @return bool
     */
    public function forceDelete(User $authUser, User $user): bool
    {
        return $authUser->can('ForceDelete:User');
    }

    /**
     * force delete any.
     *
     * @param  User  $authUser  user

     * @return bool
     */
    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:User');
    }

    /**
     * restore any.
     *
     * @param  User  $authUser  user

     * @return bool
     */
    public function restoreAny(User $authUser): bool
    {
        return $authUser->can('RestoreAny:User');
    }

    /**
     * replicate.
     *
     * @param  User  $authUser  user
     * @param  User  $user  пользователь

     * @return bool
     */
    public function replicate(User $authUser, User $user): bool
    {
        return $authUser->can('Replicate:User');
    }

    /**
     * reorder.
     *
     * @param  User  $authUser  user

     * @return bool
     */
    public function reorder(User $authUser): bool
    {
        return $authUser->can('Reorder:User');
    }
}
