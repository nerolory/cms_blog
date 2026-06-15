<?php

namespace App\Filament\Resources\TokenPackages\Pages;

use App\Filament\Resources\TokenPackages\TokenPackageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

/**
 * Компонент Filament edit token package.
 */
class EditTokenPackage extends EditRecord
{
    protected static string $resource = TokenPackageResource::class;

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
