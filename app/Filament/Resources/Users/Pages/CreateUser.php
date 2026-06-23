<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Concerns\EnsuresUserHasRole;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Компонент Filament create user.
 */
class CreateUser extends CreateRecord
{
    use EnsuresUserHasRole;

    protected static string $resource = UserResource::class;

    /**
     * after create.
     */
    protected function afterCreate(): void
    {
        $this->ensureUserHasRole();
    }
}
