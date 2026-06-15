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
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Create a post via PostMutationPipeline (PostService).
 *
 * @property-read PostServiceContract $postService
 * @property-read SeoServiceContract $seoService
 */
class CreatePost extends CreateRecord
{
    use PreviewsPostFromFilament;

    protected static string $resource = PostResource::class;

    protected PostServiceContract $postService;

    protected SeoServiceContract $seoService;

    /**
     * Внедряет сервисы постов и SEO для admin create.
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
        return [$this->previewPostAction()];
    }

    /**
     * preview source post.

     *
     * @return ?Post
     */
    protected function previewSourcePost(): ?Post
    {
        return null;
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
     * handle record creation.
     *
     * @param  array<string, mixed>  $data
     * @return Model
     */
    protected function handleRecordCreation(array $data): Model
    {
        /** @var User $actor */
        $actor = auth()->user();
        $formState = FilamentPostFormState::fromArray($data);
        /** @var Post $post */
        $post = $this->postService->createFromAdmin($formState->toPostData(), $actor);
        $this->seoService->updateFromFilament($post, $formState->toSeoData());

        return $post;
    }
}
