<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

/**
 * Компонент Filament user form.
 */
class UserForm
{
    /**
     * configure.
     *
     * @param  Schema  $schema  схема Filament

     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([TextInput::make('name')->label(__('admin.users.fields.name'))
            ->required()->maxLength(255), TextInput::make('email')->label(__('admin.users.fields.email'))->email()
            ->required()->maxLength(255), TextInput::make('password')->label(__('admin.users.fields.password'))
            ->password()->dehydrated(fn (?string $state): bool => filled($state))
            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
            ->required(fn (string $operation): bool => $operation === 'create'), TextInput::make('theme')
            ->label(__('admin.users.fields.theme'))->default('default')->disabled()
            ->dehydrated(false), TextInput::make('avatar_path')->label(__('admin.users.fields.avatar'))->disabled()
            ->dehydrated(false), Select::make('roles')->label(__('admin.users.fields.roles'))
            ->relationship('roles', 'name')->multiple()->preload()->columnSpanFull()]);
    }
}
