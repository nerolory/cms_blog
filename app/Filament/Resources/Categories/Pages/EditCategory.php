<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\EditRecord;

/**
 * Компонент Filament edit category.
 */
class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;
}
