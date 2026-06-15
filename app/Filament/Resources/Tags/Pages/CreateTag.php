<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Tags\TagResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Компонент Filament create tag.
 */
class CreateTag extends CreateRecord
{
    protected static string $resource = TagResource::class;
}
