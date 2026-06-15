<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Компонент Filament list categories.
 */
class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;
}
