<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Tags\TagResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Компонент Filament list tags.
 */
class ListTags extends ListRecords
{
    protected static string $resource = TagResource::class;
}
