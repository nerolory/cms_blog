<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Post;
use App\Models\User;
use App\Services\Contracts\PostServiceContract;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

/**
 * Компонент Filament posts table.
 */
class PostsTable
{
    /**
     * configure.

     *
     * @return Table
     */
    public static function configure(Table $table): Table
    {
        return $table->columns([ImageColumn::make('featured_image_path')->label(__('admin.posts.fields.featured_image'))
            ->disk('public')->square()->toggleable(), TextColumn::make('title')->label(__('admin.posts.fields.title'))
            ->searchable()->sortable(), TextColumn::make('user.name')->label(__('admin.posts.fields.author'))
            ->searchable()->sortable(), TextColumn::make('status')->label(__('admin.posts.fields.status'))->badge()
            ->formatStateUsing(fn (PostStatus $state): string => __('posts.status.'.$state->value))
            ->color(fn (PostStatus $state): string => match ($state) {
                PostStatus::Draft => 'gray',
                PostStatus::PendingModeration => 'warning',
                PostStatus::Published => 'success',
                PostStatus::Rejected => 'danger',
            })->sortable(), TextColumn::make('visibility')->label(__('admin.posts.fields.visibility'))
            ->formatStateUsing(fn (Post $record): string => self::formatVisibility($record))
            ->toggleable(), TextColumn::make('published_at')->label(__('admin.posts.fields.published_at'))->dateTime()
            ->sortable(), TextColumn::make('updated_at')->dateTime()->sortable()
            ->toggleable(isToggledHiddenByDefault: true)])->defaultSort('updated_at', 'desc')
            ->filters([SelectFilter::make('status')->label(__('admin.posts.filters.status'))
                ->options(collect(PostStatus::cases())->mapWithKeys(fn (PostStatus $status): array => [$status
                    ->value => __('posts.status.'.$status->value)])->all()), SelectFilter::make('visibility')
                ->label(__('admin.posts.filters.visibility'))->options(collect(PostVisibility::cases())
                ->mapWithKeys(fn (PostVisibility $visibility): array => [$visibility
                    ->value => __('posts.visibility.'.$visibility->value)])->all()), SelectFilter::make('user_id')
                ->label(__('admin.posts.filters.author'))->relationship('user', 'name')->searchable()
                ->preload(), TrashedFilter::make()])->recordActions([Action::make('approve')
            ->label(__('admin.posts.actions.approve'))->icon('heroicon-o-check-circle')->color('success')
            ->visible(fn (Post $record): bool => auth()->user() instanceof User && auth()->user()
                ->can('moderate', $record))->action(function (Post $record, PostServiceContract $postService): void {
                    $moderator = auth()->user();
                    if (! $moderator instanceof User) {
                        return;
                    }
                    $postService->approve($record, $moderator);
                }), Action::make('reject')->label(__('admin.posts.actions.reject'))->icon('heroicon-o-x-circle')
            ->color('danger')->visible(fn (Post $record): bool => auth()->user() instanceof User && auth()->user()
            ->can('moderate', $record))->schema([Textarea::make('reason')
            ->label(__('admin.posts.actions.rejection_reason'))->required()])
            ->action(function (Post $record, array $data, PostServiceContract $postService): void {
                $moderator = auth()->user();
                if (! $moderator instanceof User) {
                    return;
                }
                $postService->reject($record, $moderator, $data['reason']);
            }), EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()
            ->action(function (Post $record, PostServiceContract $postService): void {
                $postService->destroy($record);
            }), ForceDeleteBulkAction::make(), RestoreBulkAction::make()])]);
    }

    private static function formatVisibility(Post $record): string
    {
        $enumVisibility = PostVisibility::tryFrom($record->visibility);
        if ($enumVisibility === PostVisibility::Permission) {
            $permissionName = $record->requiredPermission?->name;
            if ($permissionName !== null) {
                $label = trans('permissions.'.$permissionName);

                return is_string($label) && $label !== 'permissions.'.$permissionName ? $label : $permissionName;
            }

            return __('posts.visibility.permission');
        }
        if ($enumVisibility !== null) {
            return __('posts.visibility.'.$enumVisibility->value);
        }

        return $record->visibility;
    }
}
