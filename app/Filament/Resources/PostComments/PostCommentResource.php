<?php

namespace App\Filament\Resources\PostComments;

use App\Enums\CommentStatus;
use App\Filament\Resources\PostComments\Pages\ListPostComments;
use App\Models\PostComment;
use App\Services\Contracts\CommentServiceContract;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Компонент Filament post comment resource.
 */
class PostCommentResource extends Resource
{
    protected static ?string $model = PostComment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?int $navigationSort = 22;

    /**
     * Возвращает navigation group.

     *
     * @return ?string
     */
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.content');
    }

    /**
     * Возвращает navigation label.

     *
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.comments.navigation');
    }

    /**
     * table.

     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('post.title')
            ->limit(40), TextColumn::make('user.name'), TextColumn::make('body')->limit(80), TextColumn::make('status')
            ->badge(), TextColumn::make('created_at')->dateTime()])->recordActions([Action::make('hide')
            ->label(__('admin.comments.actions.hide'))->visible(fn (PostComment $record): bool => $record
            ->status === CommentStatus::Visible)->action(function (
                PostComment $record,
                CommentServiceContract $commentService,
            ): void {
                $commentService->hide($record);
            }), DeleteAction::make()->action(function (
                PostComment $record,
                CommentServiceContract $commentService,
            ): void {
                $commentService->delete($record);
            })]);
    }

    /**
     * Возвращает pages.
     *
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return ['index' => ListPostComments::route('/')];
    }
}
