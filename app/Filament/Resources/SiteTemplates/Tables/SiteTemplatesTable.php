<?php

namespace App\Filament\Resources\SiteTemplates\Tables;

use App\Models\SiteTemplate;
use App\Services\Contracts\SiteTemplateServiceContract;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Компонент Filament site templates table.
 */
class SiteTemplatesTable
{
    /**
     * configure.

     *
     * @return Table
     */
    public static function configure(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label(__('admin.site_templates.fields.name'))->searchable()
            ->sortable(), TextColumn::make('slug')->label(__('admin.site_templates.fields.slug'))
            ->searchable(), TextColumn::make('view_prefix')->label(__('admin.site_templates.fields.view_prefix'))
            ->toggleable(), IconColumn::make('is_active')->label(__('admin.site_templates.fields.is_active'))
            ->boolean(), IconColumn::make('is_default')->label(__('admin.site_templates.fields.is_default'))
            ->boolean(), TextColumn::make('themes_count')->label(__('admin.site_templates.fields.themes_count'))
            ->counts('themes')])->recordActions([Action::make('activate')
            ->label(__('admin.site_templates.actions.activate'))->icon('heroicon-o-check-circle')
            ->visible(fn (SiteTemplate $record): bool => ! $record->is_active)
            ->action(function (SiteTemplate $record, SiteTemplateServiceContract $siteTemplateService): void {
                $siteTemplateService->setActiveTemplate($record);
                Notification::make()->success()->title(__('admin.site_templates.notifications.activated'))->send();
            }), EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
