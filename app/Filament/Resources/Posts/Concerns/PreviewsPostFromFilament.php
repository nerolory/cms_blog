<?php

namespace App\Filament\Resources\Posts\Concerns;

use App\DTO\FilamentPostFormState;
use App\DTO\PostPreviewMediaInput;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\User;
use App\Services\Contracts\PostPreviewServiceContract;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

/**
 * Компонент Filament previews post from filament.
 *
 * @property-read PostPreviewServiceContract $postPreviewService
 */
trait PreviewsPostFromFilament
{
    protected PostPreviewServiceContract $postPreviewService;

    /**
     * Внедряет сервис preview через Livewire boot hook.
     */
    public function bootPreviewsPostFromFilament(PostPreviewServiceContract $postPreviewService): void
    {
        $this->postPreviewService = $postPreviewService;
    }

    /**
     * preview source post.

     *
     * @return ?Post
     */
    abstract protected function previewSourcePost(): ?Post;

    /**
     * preview post action.

     *
     * @return Action
     */
    protected function previewPostAction(): Action
    {
        return Action::make('preview')->label(__('admin.posts.actions.preview'))->icon(Heroicon::OutlinedEye)
            ->action(function (): void {
                /** @var User $actor */
                $actor = auth()->user();
                $post = $this->previewSourcePost();
                $backUrl = $post instanceof Post ? PostResource::getUrl('edit', ['record' => $post])
                    : PostResource::getUrl('create');
                $url = $this->postPreviewService->store(
                    FilamentPostFormState::fromArray($this->form->getState())->toPostData(), $actor, $post, $backUrl,
                    new PostPreviewMediaInput);
                $this->js('window.open('.json_encode($url).', "_blank")');
            });
    }
}
