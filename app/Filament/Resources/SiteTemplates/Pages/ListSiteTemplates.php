<?php

namespace App\Filament\Resources\SiteTemplates\Pages;

use App\Filament\Resources\SiteTemplates\SiteTemplateResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * Компонент Filament list site templates.
 */
class ListSiteTemplates extends ListRecords
{
    protected static string $resource = SiteTemplateResource::class;

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
