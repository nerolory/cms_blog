<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Компонент Filament user resource.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    /**
     * Возвращает navigation label.

     *
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.users.navigation');
    }

    /**
     * Возвращает model label.

     *
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.users.model');
    }

    /**
     * Возвращает plural model label.

     *
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.users.plural');
    }

    /**
     * Возвращает navigation group.

     *
     * @return ?string
     */
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.access');
    }

    /**
     * form.
     *
     * @param  Schema  $schema  схема Filament

     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    /**
     * table.

     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    /**
     * Возвращает relations.
     *
     * @return array<string, class-string<RelationManager>>
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Возвращает pages.
     *
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return ['index' => ListUsers::route('/'), 'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit')];
    }
}
