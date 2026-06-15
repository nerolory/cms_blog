<?php

namespace App\Filament\Resources\Posts\Pages;

use App\DTO\FilamentPostFormState;
use App\Filament\Resources\Posts\Concerns\PreviewsPostFromFilament;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\User;
use App\Services\Contracts\PostServiceContract;
use App\Services\Contracts\SeoServiceContract;
use App\Support\FilamentPostBody;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Edit a post via PostMutationPipeline (PostService).
 *
 * @property-read PostServiceContract $postService
 * @property-read SeoServiceContract $seoService
 */
class EditPost extends EditRecord
{
    use PreviewsPostFromFilament;

    protected static string $resource = PostResource::class;

    protected PostServiceContract $postService;

    protected SeoServiceContract $seoService;

    /**
     * Внедряет сервисы постов и SEO для admin edit.
     */
    public function boot(PostServiceContract $postService, SeoServiceContract $seoService): void
    {
        $this->postService = $postService;
        $this->seoService = $seoService;
    }

    /**
     * Возвращает header actions.
     *
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [$this->previewPostAction(), DeleteAction::make()->action(function (Post $record): void {
            $this->postService->destroy($record);
            $this->redirect($this->getResource()::getUrl('index'));
        }), ForceDeleteAction::make(), RestoreAction::make()->action(function (Post $record): void {
            $restored = $this->postService->restore($record);
            $this->record = $restored;
            $this->fillForm();
        })];
    }

    /**
     * preview source post.

     *
     * @return ?Post
     */
    protected function previewSourcePost(): ?Post
    {
        $record = $this->record;

        return $record instanceof Post ? $record : null;
    }

    /**
     * mutate form data before fill.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return FilamentPostBody::prepareForFill($data);
    }

    /**
     * handle record update.
     *
     * @param  array<string, mixed>  $data
     * @return Model
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Post $record */
        /** @var User $actor */
        $actor = auth()->user();
        $formState = FilamentPostFormState::fromArray($data);

        return tap($this->postService->updateFromAdmin($formState->toPostData(), $record, $actor),
            fn (Post $post) => $this->seoService->updateFromFilament($post, $formState->toSeoData()));
    }
}
