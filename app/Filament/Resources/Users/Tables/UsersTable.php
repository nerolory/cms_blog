<?php

namespace App\Filament\Resources\Users\Tables;

use App\DTO\TokenGrantData;
use App\Enums\AccountStatus;
use App\Models\User;
use App\Services\Contracts\TokenWalletServiceContract;
use App\Services\Contracts\UserServiceContract;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Компонент Filament users table.
 */
class UsersTable
{
    /**
     * configure.
     *
     * @return Table
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.users.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('admin.users.fields.email'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('email_verified_at')
                    ->label(__('admin.users.fields.email_verified'))
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->hasVerifiedEmail()),
                TextColumn::make('account_status')
                    ->label(__('admin.users.fields.account_status'))
                    ->badge()
                    ->formatStateUsing(
                        fn (AccountStatus $state): string => __('admin.users.account_status.'.$state->value),
                    )
                    ->color(fn (AccountStatus $state): string => match ($state) {
                        AccountStatus::Pending => 'warning',
                        AccountStatus::Active => 'success',
                        AccountStatus::Suspended => 'danger',
                    }),
                TextColumn::make('roles.name')
                    ->label(__('admin.users.fields.roles'))
                    ->badge(),
                TextColumn::make('theme')
                    ->label(__('admin.users.fields.theme'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('token_wallet.balance')
                    ->label(__('admin.users.fields.token_balance'))
                    ->default(0)
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label(__('admin.users.filters.email_verified'))
                    ->nullable()
                    ->trueLabel(__('admin.users.filters.email_verified_yes'))
                    ->falseLabel(__('admin.users.filters.email_verified_no'))
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('email_verified_at'),
                        false: fn ($query) => $query->whereNull('email_verified_at'),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('activate')
                    ->label(__('admin.users.actions.activate'))
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (User $record): bool => ! $record->isAccountActive()
                        && auth()->user()?->can('users.activate') === true)
                    ->action(function (User $record, UserServiceContract $userService): void {
                        $userService->activate($record);
                        Notification::make()->success()->title(__('admin.users.notifications.activated'))->send();
                    }),
                Action::make('deactivate')
                    ->label(__('admin.users.actions.deactivate'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $record): bool => $record->isAccountActive()
                        && ! $record->isAccountSuspended()
                        && auth()->user()?->can('users.activate') === true)
                    ->requiresConfirmation()
                    ->action(function (User $record, UserServiceContract $userService): void {
                        $userService->deactivate($record);
                        Notification::make()->success()->title(__('admin.users.notifications.deactivated'))->send();
                    }),
                Action::make('suspend')
                    ->label(__('admin.users.actions.suspend'))
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (User $record): bool => ! $record->isAccountSuspended()
                        && auth()->user()?->can('users.activate') === true)
                    ->requiresConfirmation()
                    ->action(function (User $record, UserServiceContract $userService): void {
                        $userService->suspend($record);
                        Notification::make()->success()->title(__('admin.users.notifications.suspended'))->send();
                    }),
                Action::make('grantTokens')
                    ->label(__('admin.users.actions.grant_tokens'))
                    ->icon('heroicon-o-gift')
                    ->visible(fn (): bool => auth()->user()?->can('settings.manage') === true)
                    ->schema([
                        TextInput::make('amount')
                            ->label(__('admin.users.fields.grant_amount'))
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Textarea::make('note')
                            ->label(__('admin.users.fields.grant_note'))
                            ->rows(2),
                    ])
                    ->action(function (
                        User $record,
                        array $data,
                        TokenWalletServiceContract $tokenWalletService,
                    ): void {
                        /** @var User $admin */
                        $admin = auth()->user();
                        $tokenWalletService->grant(new TokenGrantData(
                            userId: $record->id,
                            amount: (int) $data['amount'],
                            grantedByUserId: $admin->id,
                            note: $data['note'] ?? null,
                        ));
                        Notification::make()->success()->title(__('admin.users.notifications.tokens_granted'))->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
