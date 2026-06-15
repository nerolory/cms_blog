<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Компонент Filament create user.
 */
class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
