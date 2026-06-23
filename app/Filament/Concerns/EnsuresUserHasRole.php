<?php

namespace App\Filament\Concerns;

use App\Models\User;
use App\Support\Rbac\DefaultUserRoleAssigner;

/**
 * Страховка Filament: после сохранения у пользователя есть хотя бы
 * роль user.
 */
trait EnsuresUserHasRole
{
    /**
     * ensure user has role.
     */
    protected function ensureUserHasRole(): void
    {
        $record = $this->record;
        if (! $record instanceof User) {
            return;
        }

        app(DefaultUserRoleAssigner::class)->assignIfMissing($record);
    }
}
