<?php

namespace App\Filament\Concerns;

use App\Support\Rbac\DefaultUserRoleAssigner;

/**
 * Страховка Filament: после сохранения у пользователя есть хотя бы роль user.
 */
trait EnsuresUserHasRole
{
    protected function ensureUserHasRole(): void
    {
        app(DefaultUserRoleAssigner::class)->assignIfMissing($this->record);
    }
}
