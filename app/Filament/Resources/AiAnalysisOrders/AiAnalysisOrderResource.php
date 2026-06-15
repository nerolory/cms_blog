<?php

namespace App\Filament\Resources\AiAnalysisOrders;

use App\Enums\AiAnalysisOrderStatus;
use App\Filament\Resources\AiAnalysisOrders\Pages\ListAiAnalysisOrders;
use App\Models\AiAnalysisOrder;
use App\Models\User;
use App\Services\Contracts\AiAnalysisOrderServiceContract;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Компонент Filament ai analysis order resource.
 */
class AiAnalysisOrderResource extends Resource
{
    protected static ?string $model = AiAnalysisOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 23;

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
        return __('admin.ai_orders.navigation');
    }

    /**
     * Возвращает eloquent query.
     *
     * @return Builder<AiAnalysisOrder>
     */
    public static function getEloquentQuery(): Builder
    {
        /** @var Builder<AiAnalysisOrder> $query */
        $query = parent::getEloquentQuery()->with(['user', 'post', 'reviewer']);

        return $query;
    }

    /**
     * Проверяет возможность access.

     *
     * @return bool
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->can('ai.orders.manage');
    }

    /**
     * table.

     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([TextColumn::make('id')
            ->sortable(), TextColumn::make('post.title')->limit(40)->searchable(), TextColumn::make('user.name')
            ->label(__('admin.ai_orders.fields.user')), TextColumn::make('comment_count')
            ->label(__('admin.ai_orders.fields.comment_count')), TextColumn::make('tokens_required')
            ->label(__('admin.ai_orders.fields.tokens_required')), TextColumn::make('status')->badge()
            ->formatStateUsing(fn (AiAnalysisOrderStatus $state): string => __('admin.ai_orders.status.'.$state
                ->value)), TextColumn::make('created_at')->dateTime()->sortable()])
            ->filters([SelectFilter::make('status')
                ->options(collect(AiAnalysisOrderStatus::cases())
                    ->mapWithKeys(fn (AiAnalysisOrderStatus $status): array => [$status
                        ->value => __('admin.ai_orders.status.'.$status->value)])->all())])
            ->recordActions([Action::make('approve')
                ->label(__('admin.ai_orders.actions.approve'))->icon('heroicon-o-check')->color('success')
                ->visible(fn (AiAnalysisOrder $record): bool => $record->status === AiAnalysisOrderStatus::Pending)
                ->schema([Textarea::make('admin_note')->label(__('admin.ai_orders.fields.admin_note'))->rows(2)])
                ->action(function (
                    AiAnalysisOrder $record,
                    array $data,
                    AiAnalysisOrderServiceContract $orderService,
                ): void {
                    /** @var User $reviewer */
                    $reviewer = auth()->user();
                    $orderService->approve(
                        $record,
                        $reviewer,
                        $data['admin_note'] ?? null,
                    );
                    Notification::make()->success()->title(__('admin.ai_orders.notifications.approved'))->send();
                }), Action::make('reject')->label(__('admin.ai_orders.actions.reject'))->icon('heroicon-o-x-mark')
                ->color('danger')->visible(fn (AiAnalysisOrder $record): bool => $record
                ->status === AiAnalysisOrderStatus::Pending)->requiresConfirmation()
                ->schema([Textarea::make('admin_note')
                    ->label(__('admin.ai_orders.fields.admin_note'))->rows(2)])
                ->action(function (
                    AiAnalysisOrder $record,
                    array $data,
                    AiAnalysisOrderServiceContract $orderService,
                ): void {
                    /** @var User $reviewer */
                    $reviewer = auth()->user();
                    $orderService->reject($record, $reviewer, $data['admin_note'] ?? null);
                    Notification::make()->success()->title(__('admin.ai_orders.notifications.rejected'))->send();
                }), Action::make('execute')->label(__('admin.ai_orders.actions.execute'))->icon('heroicon-o-play')
                ->color('primary')->visible(fn (AiAnalysisOrder $record): bool => $record
                ->status === AiAnalysisOrderStatus::Approved)->requiresConfirmation()
                ->modalDescription(__('admin.ai_orders.actions.execute_confirm'))
                ->action(function (AiAnalysisOrder $record, AiAnalysisOrderServiceContract $orderService): void {
                    /** @var User $executor */
                    $executor = auth()->user();
                    try {
                        $orderService->execute($record, $executor);
                        Notification::make()->success()->title(__('admin.ai_orders.notifications.executed'))->send();
                    } catch (\Throwable $exception) {
                        Notification::make()->danger()->title($exception->getMessage())->send();
                    }
                })]);
    }

    /**
     * Возвращает pages.
     *
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return ['index' => ListAiAnalysisOrders::route('/')];
    }
}
