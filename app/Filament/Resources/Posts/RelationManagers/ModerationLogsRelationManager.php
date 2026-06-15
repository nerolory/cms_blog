<?php

namespace App\Filament\Resources\Posts\RelationManagers;

use App\Enums\PostModerationAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Компонент Filament moderation logs relation manager.
 */
class ModerationLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'moderationLogs';

    /**
     * Возвращает title.
     *
     * @param  Model  $ownerRecord  record
     * @param  string  $pageClass  class

     * @return string
     */
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.posts.moderation_log.title');
    }

    /**
     * table.

     *
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('admin.posts.moderation_log.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('action')
                    ->label(__('admin.posts.moderation_log.action'))
                    ->badge()
                    ->formatStateUsing(
                        function (PostModerationAction $state): string {
                            return __('admin.posts.moderation_log.actions.'.$state->value);
                        },
                    ),
                TextColumn::make('actor.name')
                    ->label(__('admin.posts.moderation_log.actor'))
                    ->placeholder('—'),
                TextColumn::make('reason')
                    ->label(__('admin.posts.moderation_log.reason'))
                    ->wrap()
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25])
            ->recordActions([])
            ->toolbarActions([]);
    }

    /**
     * Проверяет read only.

     *
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return true;
    }
}
