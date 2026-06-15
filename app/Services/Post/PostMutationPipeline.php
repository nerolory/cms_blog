<?php

namespace App\Services\Post;

use App\DTO\PostData;
use App\DTO\PostMediaData;
use App\DTO\PostWebMediaSyncInput;
use App\Enums\PostModerationAction;
use App\Enums\PostStatus;
use App\Events\PostPublished;
use App\Events\PostSubmitted;
use App\Events\PostUpdated;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostApprovedNotification;
use App\Notifications\PostRejectedNotification;
use App\Repositories\Contracts\FileRepositoryContract;
use App\Repositories\Contracts\PostModerationLogRepositoryContract;
use App\Repositories\Contracts\PostRepositoryContract;
use App\Repositories\Contracts\TagRepositoryContract;
use App\Services\Contracts\PostVersionServiceContract;
use App\Services\Contracts\StoredImageOptimizationServiceContract;
use App\Support\Cache\CacheVersionManager;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;

/**
 * Single entry point for all post mutations (create, update, moderate, media, restore).
 *
 * @property-read PostRepositoryContract $postRepository
 * @property-read PostVersionServiceContract $versionService
 * @property-read PostModerationLogRepositoryContract $moderationLogRepository
 * @property-read TagRepositoryContract $tagRepository
 * @property-read CacheVersionManager $cacheVersions
 * @property-read FileRepositoryContract $fileRepository
 * @property-read StoredImageOptimizationServiceContract $imageOptimizer
 */
final class PostMutationPipeline
{
    private const FEATURED_MAX_WIDTH = 1920;

    private const BACKGROUND_MAX_WIDTH = 2560;

    public function __construct(private PostRepositoryContract $postRepository,
        private PostVersionServiceContract $versionService,
        private PostModerationLogRepositoryContract $moderationLogRepository,
        private TagRepositoryContract $tagRepository, private CacheVersionManager $cacheVersions,
        private FileRepositoryContract $fileRepository,
        private StoredImageOptimizationServiceContract $imageOptimizer) {}

    /**
     * Создаёт from admin.
     *
     * @param  PostData  $data  данные формы

     * @return Post
     */
    public function createFromAdmin(PostData $data, User $actor): Post
    {
        $post = $this->postRepository->create($data);
        $this->syncTaxonomy($post, $data);
        $this->recordCreateModeration($post, $data->status, $actor);
        $this->dispatchCreateEvents($post, $data->status, $actor);
        $this->cacheVersions->bumpPosts();

        return $post;
    }

    /**
     * Создаёт for author.
     *
     * @param  PostData  $data  данные формы

     * @return Post
     */
    public function createForAuthor(PostData $data, User $author): Post
    {
        $authorData = PostData::forAuthorSubmission(title: $data->title, slug: $data->slug, excerpt: $data->excerpt,
            body: $data->body, userId: $author->id, visibility: $data->visibility,
            requiredPermissionId: $data->required_permission_id, themePrimaryColor: $data->theme_primary_color,
            themeAccentColor: $data->theme_accent_color, contentOpacity: $data->content_opacity,
            editorMode: $data->editor_mode, categoryId: $data->category_id, tagIds: $data->tag_ids);
        $post = $this->postRepository->create($authorData);
        $this->syncTaxonomy($post, $authorData);
        $this->moderationLogRepository->record($post, PostModerationAction::Submitted, $author);
        Event::dispatch(new PostSubmitted($post, $author));
        $this->cacheVersions->bumpPosts();

        return $post;
    }

    /**
     * Обновляет from admin.
     *
     * @param  PostData  $data  данные формы
     * @param  Post  $post  пост

     * @return Post
     */
    public function updateFromAdmin(PostData $data, Post $post, User $actor): Post
    {
        $previousStatus = $post->status;
        $this->versionService->snapshotBeforeUpdate($post, $actor);
        $this->postRepository->update($data, $post);
        $this->syncTaxonomy($post, $data);
        $fresh = $this->postRepository->refreshWithRelations($post);
        $this->moderationLogRepository->record($fresh, PostModerationAction::Updated, $actor);
        Event::dispatch(new PostUpdated($fresh, $actor));
        if ($previousStatus !== PostStatus::Published && $data->status === PostStatus::Published) {
            Event::dispatch(new PostPublished($fresh, $actor));
        }
        $this->cacheVersions->bumpPosts();

        return $fresh;
    }

    /**
     * Обновляет from web.
     *
     * @param  PostData  $data  данные формы
     * @param  Post  $post  пост

     * @return bool
     */
    public function updateFromWeb(PostData $data, Post $post, User $editor): bool
    {
        $status = $this->resolveWebUpdateStatus($data, $editor, $post);
        $previousStatus = $post->status;
        $this->versionService->snapshotBeforeUpdate($post, $editor);
        $resolved = $this->buildWebUpdateData($data, $post, $status);
        if (! $this->postRepository->update($resolved, $post)) {
            return false;
        }
        $this->syncTaxonomy($post, $resolved);
        $fresh = $this->postRepository->refreshWithRelations($post);
        $this->cacheVersions->bumpPosts();
        $this->recordWebUpdateSideEffects($fresh, $editor, $previousStatus, $status);

        return true;
    }

    /**
     * Утверждает пост модератором.

     *
     * @return Post
     */
    public function approve(Post $post, User $moderator): Post
    {
        if (! $moderator->can('posts.moderate')) {
            throw new AuthorizationException(__('posts.auth.moderation_denied'));
        }
        $post = $this->postRepository->approve($post);
        $this->cacheVersions->bumpPosts();
        $this->moderationLogRepository->record($post, PostModerationAction::Approved, $moderator);
        Event::dispatch(new PostPublished($post, $moderator));
        if ($post->user !== null) {
            $post->user->notify(new PostApprovedNotification($post));
        }

        return $post;
    }

    /**
     * Отклоняет пост с указанием причины.

     *
     * @return Post
     */
    public function reject(Post $post, User $moderator, string $reason): Post
    {
        if (! $moderator->can('posts.moderate')) {
            throw new AuthorizationException(__('posts.auth.moderation_denied'));
        }
        $reason = trim($reason);
        $post = $this->postRepository->reject($post, $reason);
        $this->cacheVersions->bumpPosts();
        $this->moderationLogRepository->record($post, PostModerationAction::Rejected, $moderator, $reason);
        if ($post->user !== null) {
            $post->user->notify(new PostRejectedNotification($post, $reason));
        }

        return $post;
    }

    /**
     * Удаляет пост из хранилища.
     *
     * @param  Post  $post  пост

     * @return ?bool
     */
    public function destroy(Post $post): ?bool
    {
        $deleted = $this->postRepository->destroy($post);
        if ($deleted) {
            $this->cacheVersions->bumpPosts();
        }

        return $deleted;
    }

    /**
     * Восстанавливает удалённый пост.
     *
     * @param  Post  $post  пост

     * @return Post
     */
    public function restore(Post $post): Post
    {
        $restored = $this->postRepository->restore($post);
        $this->cacheVersions->bumpPosts();

        return $restored;
    }

    /**
     * Синхронизирует featured/background медиа из web-формы.
     *
     * @param  Post  $post  пост
     * @param  PostWebMediaSyncInput  $input  загрузки и флаги удаления

     * @return Post
     */
    public function syncWebMedia(Post $post, PostWebMediaSyncInput $input): Post
    {
        $featuredPath = $post->featured_image_path;
        $backgroundPath = $post->background_image_path;
        $featuredChanged = $this->syncFeaturedMediaPath($featuredPath, $input->featuredImage,
            $input->removeFeaturedImage);
        $backgroundChanged = $this->syncBackgroundMediaPath($backgroundPath, $input->backgroundImage,
            $input->removeBackgroundImage);
        if (! $featuredChanged && ! $backgroundChanged) {
            return $post;
        }
        $updated = $this->postRepository->updateMediaPaths($post, new PostMediaData($featuredPath, $backgroundPath));
        $this->cacheVersions->bumpPosts();

        return $updated;
    }

    /**
     * @param-out ?string $path
     */
    private function syncFeaturedMediaPath(?string &$path, ?UploadedFile $upload, bool $remove): bool
    {
        return $this->applyWebMediaPath($path, $upload, $remove, 'posts/featured', self::FEATURED_MAX_WIDTH);
    }

    /**
     * @param-out ?string $path
     */
    private function syncBackgroundMediaPath(?string &$path, ?UploadedFile $upload, bool $remove): bool
    {
        return $this->applyWebMediaPath($path, $upload, $remove, 'posts/background', self::BACKGROUND_MAX_WIDTH);
    }

    private function resolveWebUpdateStatus(PostData $data, User $editor, Post $post): PostStatus
    {
        $status = $post->status;
        if ($editor->can('posts.manage.all') && $data->is_published) {
            return PostStatus::Published;
        }
        if (! $editor->can('posts.manage.all')) {
            return PostStatus::PendingModeration;
        }

        return $status;
    }

    private function buildWebUpdateData(PostData $data, Post $post, PostStatus $status): PostData
    {
        return PostData::fromValidated(title: $data->title, slug: $data->slug, excerpt: $data->excerpt,
            body: $data->body, isPublished: $data->is_published, userId: $data->user_id, status: $status,
            visibility: $data->visibility, requiredPermissionId: $data->required_permission_id,
            featuredImagePath: $post->featured_image_path, backgroundImagePath: $post->background_image_path,
            themePrimaryColor: $data->theme_primary_color, themeAccentColor: $data->theme_accent_color,
            contentOpacity: $data->content_opacity, editorMode: $data->editor_mode);
    }

    private function recordWebUpdateSideEffects(Post $fresh, User $editor, PostStatus $previousStatus,
        PostStatus $status): void
    {
        $this->moderationLogRepository->record($fresh, PostModerationAction::Updated, $editor);
        Event::dispatch(new PostUpdated($fresh, $editor));
        if ($previousStatus !== PostStatus::Published && $status === PostStatus::Published) {
            Event::dispatch(new PostPublished($fresh, $editor));
        }
        if ($previousStatus === PostStatus::Published && $status === PostStatus::PendingModeration) {
            $this->moderationLogRepository->record($fresh, PostModerationAction::Submitted, $editor);
            Event::dispatch(new PostSubmitted($fresh, $editor));
        }
    }

    /**
     * @param-out ?string $path
     */
    private function applyWebMediaPath(?string &$path, ?UploadedFile $upload, bool $remove, string $directory,
        int $maxWidth): bool
    {
        if ($remove) {
            $this->deletePublicMedia($path);
            $path = null;

            return true;
        }
        if ($upload instanceof UploadedFile) {
            $this->deletePublicMedia($path);
            $path = $this->storeUploadOptimized($upload, $directory, $maxWidth);

            return true;
        }

        return false;
    }

    private function deletePublicMedia(?string $path): void
    {
        $this->fileRepository->deletePublic($path);
        $this->cacheVersions->bumpMediaFor($path);
    }

    private function recordCreateModeration(Post $post, PostStatus $status, User $actor): void
    {
        $action = match ($status) {
            PostStatus::PendingModeration => PostModerationAction::Submitted,
            PostStatus::Published => PostModerationAction::Approved,
            default => null,
        };

        if ($action !== null) {
            $this->moderationLogRepository->record($post, $action, $actor);
        }
    }

    private function dispatchCreateEvents(Post $post, PostStatus $status, User $actor): void
    {
        match ($status) {
            PostStatus::PendingModeration => Event::dispatch(new PostSubmitted($post, $actor)),
            PostStatus::Published => Event::dispatch(new PostPublished($post, $actor)),
            default => null,
        };
    }

    private function syncTaxonomy(Post $post, PostData $data): void
    {
        if ($data->category_id !== null && $post->category_id !== $data->category_id) {
            $this->postRepository->updateCategoryId($post, $data->category_id);
        }
        $this->tagRepository->syncForPost($post->id, $data->tag_ids);
        $this->cacheVersions->bumpSearch();
    }

    private function storeUploadOptimized(UploadedFile $file, string $directory, int $maxWidth): string
    {
        $binary = $this->readUploadBytes($file);
        $path = $this->fileRepository->storePublicBinary($directory, $binary);
        $this->imageOptimizer->optimizeStored($path, $maxWidth);
        $this->cacheVersions->bumpMediaFor($path);

        return $path;
    }

    private function readUploadBytes(UploadedFile $file): string
    {
        $realPath = $file->getRealPath();
        if (is_string($realPath) && is_readable($realPath)) {
            $contents = file_get_contents($realPath);
            if (is_string($contents) && $contents !== '') {
                return $contents;
            }
        }
        $contents = $file->get();

        return is_string($contents) ? $contents : '';
    }
}
