<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Concerns\EnsuresUserHasRole;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

/**
 * Компонент Filament edit user.
 */
class EditUser extends EditRecord
{
    use EnsuresUserHasRole;

    protected static string $resource = UserResource::class;

    /**
     * after save.
     */
    protected function afterSave(): void
    {
        $this->ensureUserHasRole();
    }

    /**
     * Возвращает header actions.
     *
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
