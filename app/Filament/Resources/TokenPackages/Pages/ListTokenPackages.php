<?php

namespace App\Filament\Resources\TokenPackages\Pages;

use App\Filament\Resources\TokenPackages\TokenPackageResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * Компонент Filament list token packages.
 */
class ListTokenPackages extends ListRecords
{
    protected static string $resource = TokenPackageResource::class;

    /**
     * Возвращает header actions.
     *
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
