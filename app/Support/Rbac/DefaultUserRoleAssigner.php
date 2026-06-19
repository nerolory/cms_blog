<?php

namespace App\Support\Rbac;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;

/**
 * Назначает роль user, если у учётной записи нет ни одной роли.

 *
 * @property-read RoleProvisioner $roleProvisioner
 * @property-read UserRepositoryContract $users
 */
final class DefaultUserRoleAssigner
{
    public function __construct(
        protected RoleProvisioner $roleProvisioner,
        protected UserRepositoryContract $users,
    ) {}

    /**
     * assign if missing.
     *
     * @param  User  $user
     * @return User
     */
    public function assignIfMissing(User $user): User
    {
        if ($user->roles()->count() > 0) {
            return $user;
        }

        $this->roleProvisioner->ensureUserRole();
        $this->users->assignRole($user, 'user');

        return $user->fresh() ?? $user;
    }
}
