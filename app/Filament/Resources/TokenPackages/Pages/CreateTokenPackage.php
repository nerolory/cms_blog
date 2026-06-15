<?php

namespace App\Filament\Resources\TokenPackages\Pages;

use App\Filament\Resources\TokenPackages\TokenPackageResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Компонент Filament create token package.
 */
class CreateTokenPackage extends CreateRecord
{
    protected static string $resource = TokenPackageResource::class;
}
