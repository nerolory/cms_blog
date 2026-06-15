<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Tags\TagResource;
use Filament\Resources\Pages\EditRecord;

/**
 * Компонент Filament edit tag.
 */
class EditTag extends EditRecord
{
    protected static string $resource = TagResource::class;
}
