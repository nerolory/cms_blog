<?php

namespace App\Filament\Resources\AiAnalysisOrders\Pages;

use App\Filament\Resources\AiAnalysisOrders\AiAnalysisOrderResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

/**
 * Компонент Filament list ai analysis orders.
 */
class ListAiAnalysisOrders extends ListRecords
{
    protected static string $resource = AiAnalysisOrderResource::class;

    /**
     * Возвращает header actions.
     *
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [Action::make('autoCalculation')->label(__('admin.ai_orders.actions.auto_calculation'))
            ->icon('heroicon-o-calculator')->disabled()
            ->tooltip(__('admin.ai_orders.actions.auto_calculation_tooltip'))];
    }
}
