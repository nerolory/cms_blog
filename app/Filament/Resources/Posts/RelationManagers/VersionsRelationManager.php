<?php

namespace App\Filament\Resources\Posts\RelationManagers;

use App\Models\PostVersion;
use App\Models\User;
use App\Services\Contracts\PostVersionServiceContract;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Компонент Filament versions relation manager.
 */
class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    /**
     * Возвращает title.
     *
     * @param  Model  $ownerRecord  record
     * @param  string  $pageClass  class

     * @return string
     */
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.posts.versions.title');
    }

    /**
     * table.

     *
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('version_number')->label(__('admin.posts.versions.number'))
            ->sortable(), TextColumn::make('snapshot.title')->label(__('admin.posts.fields.title'))
            ->limit(40), TextColumn::make('author.name')->label(__('admin.posts.versions.author'))
            ->placeholder('—'), TextColumn::make('created_at')->label(__('admin.posts.versions.created_at'))
            ->dateTime()->sortable()])->defaultSort('version_number', 'desc')->paginated([10, 25])
            ->recordActions([Action::make('restore')->label(__('admin.posts.versions.restore'))
                ->icon('heroicon-o-arrow-uturn-left')->requiresConfirmation()
                ->visible(fn (PostVersion $record): bool => auth()->user() instanceof User && auth()->user()
                    ->can('update', $record->post))->action(function (
                        PostVersion $record,
                        PostVersionServiceContract $versionService,
                    ): void {
                        $actor = auth()->user();
                        if (! $actor instanceof User) {
                            return;
                        }
                        $versionService->restore($record, $actor);
                        Notification::make()->success()->title(__('admin.posts.versions.restored'))->send();
                    })])->toolbarActions([]);
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
