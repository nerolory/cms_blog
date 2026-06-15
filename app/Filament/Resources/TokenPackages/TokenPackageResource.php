<?php

namespace App\Filament\Resources\TokenPackages;

use App\Filament\Resources\TokenPackages\Pages\CreateTokenPackage;
use App\Filament\Resources\TokenPackages\Pages\EditTokenPackage;
use App\Filament\Resources\TokenPackages\Pages\ListTokenPackages;
use App\Models\TokenPackage;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Компонент Filament token package resource.
 */
class TokenPackageResource extends Resource
{
    protected static ?string $model = TokenPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 31;

    /**
     * Возвращает navigation group.

     *
     * @return ?string
     */
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.system');
    }

    /**
     * Возвращает navigation label.

     *
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.token_packages.navigation');
    }

    /**
     * Проверяет возможность access.

     *
     * @return bool
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->can('settings.manage');
    }

    /**
     * form.
     *
     * @param  Schema  $schema  схема Filament

     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('name')->label(__('admin.token_packages.fields.name'))->required()
            ->maxLength(255), TextInput::make('token_amount')->label(__('admin.token_packages.fields.token_amount'))
            ->numeric()->required()->minValue(1), TextInput::make('price_cents')
            ->label(__('admin.token_packages.fields.price_cents'))->numeric()->required()
            ->minValue(0), TextInput::make('currency')->label(__('admin.token_packages.fields.currency'))
            ->default('RUB')->maxLength(3)->required(), TextInput::make('sort_order')
            ->label(__('admin.token_packages.fields.sort_order'))->numeric()->default(0), Toggle::make('is_active')
            ->label(__('admin.token_packages.fields.is_active'))->default(true)]);
    }

    /**
     * table.

     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('token_amount')->sortable(),
                TextColumn::make('price_cents')
                    ->label(__('admin.token_packages.fields.price_cents'))
                    ->formatStateUsing(
                        function (int $state, TokenPackage $record): string {
                            return number_format($state / 100, 2).' '.$record->currency;
                        },
                    ),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('sort_order')->sortable(),
            ]);
    }

    /**
     * Возвращает pages.
     *
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return ['index' => ListTokenPackages::route('/'), 'create' => CreateTokenPackage::route('/create'),
            'edit' => EditTokenPackage::route('/{record}/edit')];
    }
}
