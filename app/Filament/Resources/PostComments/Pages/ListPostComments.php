<?php

namespace App\Filament\Resources\PostComments\Pages;

use App\Filament\Resources\PostComments\PostCommentResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Компонент Filament list post comments.
 */
class ListPostComments extends ListRecords
{
    protected static string $resource = PostCommentResource::class;
}
