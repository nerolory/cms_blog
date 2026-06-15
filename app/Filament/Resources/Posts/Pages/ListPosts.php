<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\PostStatus;
use App\Filament\Resources\Posts\PostResource;
use App\Support\Post\VisibilityChecker;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post listing with a moderation queue tab.
 */
class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    /**
     * Возвращает header actions.
     *
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }

    /**
     * Возвращает tabs.
     *
     * @return array<string, mixed>
     */
    public function getTabs(): array
    {
        return ['all' => Tab::make(__('admin.posts.tabs.all')), 'pending' => Tab::make(__('admin.posts.tabs.pending'))
            ->modifyQueryUsing(function (Builder $query, VisibilityChecker $visibilityChecker): Builder {
                $query->where('status', PostStatus::PendingModeration);
                $visibilityChecker->applyModerationQueueScope($query);

                return $query;
            })];
    }
}
