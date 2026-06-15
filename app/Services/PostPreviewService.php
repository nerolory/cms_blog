<?php

namespace App\Services;

use App\DTO\PostData;
use App\DTO\PostPreviewData;
use App\DTO\PostPreviewMediaInput;
use App\Models\Post;
use App\Models\User;
use App\Presenters\PostPresenterFactory;
use App\Presenters\PostPreviewPresenter;
use App\Repositories\Contracts\FileRepositoryContract;
use App\Repositories\Contracts\PostPreviewCacheRepositoryContract;
use App\Repositories\Contracts\PostRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Services\Contracts\PostPreviewServiceContract;
use App\Support\Media\ImageProcessor;
use App\Support\TypeCast;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Сервис post preview.

 *
 * @property-read PostPreviewCacheRepositoryContract $previewCache
 * @property-read FileRepositoryContract $fileRepository
 * @property-read ImageProcessor $imageProcessor
 * @property-read PostPresenterFactory $postPresenterFactory
 * @property-read UserRepositoryContract $users
 * @property-read PostRepositoryContract $posts
 */
class PostPreviewService implements PostPreviewServiceContract
{
    public function __construct(
        protected PostPreviewCacheRepositoryContract $previewCache,
        protected FileRepositoryContract $fileRepository,
        protected ImageProcessor $imageProcessor,
        protected PostPresenterFactory $postPresenterFactory,
        protected UserRepositoryContract $users,
        protected PostRepositoryContract $posts,
    ) {}

    /**
     * {@inheritdoc}

     *
     * @return string
     */
    public function store(PostData $data, User $actor, ?Post $post, string $backUrl,
        PostPreviewMediaInput $media): string
    {
        if ($post instanceof Post) {
            $this->authorizePreview($actor, $post);
        } elseif (! $actor->can('create', Post::class)) {
            throw new AuthorizationException;
        }
        $token = (string) Str::uuid();
        $temporaryMediaPaths = [];
        $featuredPath = $this->resolveFeaturedPath($media->featuredImage, $media->removeFeaturedImage, $post, $token,
            $temporaryMediaPaths);
        $backgroundPath = $this->resolveBackgroundPath($media->backgroundImage, $media->removeBackgroundImage, $post,
            $token, $temporaryMediaPaths);
        if ($media->featuredImage === null && ! $media->removeFeaturedImage) {
            $featuredPath ??= $data->featured_image_path;
        }
        if ($media->backgroundImage === null && ! $media->removeBackgroundImage) {
            $backgroundPath ??= $data->background_image_path;
        }
        $postData = $data->withFeaturedImagePath($featuredPath)->withBackgroundImagePath($backgroundPath);

        return $this->persistPreview(preview: PostPreviewData::fromPostData(data: $postData, token: $token,
            userId: $actor->id, postId: $post?->id, backUrl: $backUrl,
            temporaryMediaPaths: $temporaryMediaPaths), actor: $actor, post: $post);
    }

    /**
     * {@inheritdoc}

     *
     * @return PostPreviewPresenter
     */
    public function resolve(string $token, User $viewer): PostPreviewPresenter
    {
        $preview = $this->previewCache->find($token);
        if ($preview === null) {
            throw new NotFoundHttpException;
        }
        if (! $this->canViewPreview($viewer, $preview)) {
            throw new AuthorizationException;
        }
        $authorId = $preview->authorUserId ?? $preview->userId;
        $author = $this->users->findById((int) $authorId);
        if ($author === null) {
            throw new NotFoundHttpException;
        }
        $transientPost = $preview->toTransientPost($author);

        return new PostPreviewPresenter($preview, $this->postPresenterFactory->for($transientPost), $transientPost);
    }

    /**
     * {@inheritdoc}

     *
     * @return int
     */
    public function cleanupExpiredMedia(): int
    {
        $directory = trim(TypeCast::string(config('post-preview.temp_directory', 'posts/preview')), '/');
        $ttlMinutes = max(1, TypeCast::int(config('post-preview.ttl_minutes', 30)));
        $threshold = now()->subMinutes($ttlMinutes)->getTimestamp();
        $removed = 0;
        if (! Storage::disk('public')->exists($directory)) {
            return 0;
        }
        foreach (Storage::disk('public')->directories($directory) as $tokenDirectory) {
            $lastModified = Storage::disk('public')->lastModified($tokenDirectory);
            if ($lastModified >= $threshold) {
                continue;
            }
            $files = Storage::disk('public')->allFiles($tokenDirectory);
            foreach ($files as $file) {
                if ($this->fileRepository->deletePublic($file)) {
                    $removed++;
                }
            }
            Storage::disk('public')->deleteDirectory($tokenDirectory);
        }

        return $removed;
    }

    private function persistPreview(PostPreviewData $preview, User $actor, ?Post $post): string
    {
        $this->previewCache->purgeForUser($actor->id);
        $this->previewCache->store($preview);

        return URL::temporarySignedRoute('posts.preview.show', now()->addMinutes(max(1,
            TypeCast::int(config('post-preview.ttl_minutes', 30)))), ['token' => $preview->token]);
    }

    private function authorizePreview(User $actor, Post $post): void
    {
        if (! $actor->can('preview', $post)) {
            throw new AuthorizationException;
        }
    }

    private function canViewPreview(User $viewer, PostPreviewData $preview): bool
    {
        if ($viewer->id === $preview->userId) {
            return true;
        }
        if ($viewer->can('posts.manage.all') || $viewer->hasRole(['admin', 'owner'])) {
            return true;
        }
        if ($preview->postId === null) {
            return false;
        }
        $post = $this->posts->findByIdOrNull((int) $preview->postId);

        return $post instanceof Post && $viewer->can('update', $post);
    }

    /**
     * @param  list<string>  $temporaryMediaPaths
     */
    private function resolveFeaturedPath(?UploadedFile $upload, bool $remove, ?Post $post, string $token,
        array &$temporaryMediaPaths): ?string
    {
        if ($remove) {
            return null;
        }
        if ($upload instanceof UploadedFile) {
            $path = $this->storeTempImage($upload, $token, 'featured', true);
            $temporaryMediaPaths[] = $path;

            return $path;
        }

        return $post?->featured_image_path;
    }

    /**
     * @param  list<string>  $temporaryMediaPaths
     */
    private function resolveBackgroundPath(?UploadedFile $upload, bool $remove, ?Post $post, string $token,
        array &$temporaryMediaPaths): ?string
    {
        if ($remove) {
            return null;
        }
        if ($upload instanceof UploadedFile) {
            $path = $this->storeTempImage($upload, $token, 'background', false);
            $temporaryMediaPaths[] = $path;

            return $path;
        }

        return $post?->background_image_path;
    }

    private function storeTempImage(UploadedFile $upload, string $token, string $role, bool $featured): string
    {
        $binary = $featured ? $this->imageProcessor->processFeaturedImage($upload) : $this->imageProcessor
            ->processBackgroundImage($upload);
        $directory = trim(TypeCast::string(config('post-preview.temp_directory', 'posts/preview')), '/').'/'.$token;

        return $this->fileRepository->storePublicBinary($directory, $binary, $role.'.jpg');
    }
}
