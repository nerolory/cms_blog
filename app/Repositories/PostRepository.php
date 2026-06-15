<?php

namespace App\Repositories;

use App\DTO\AbstractData;
use App\DTO\PostData;
use App\DTO\PostMediaData;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\PostRepositoryContract;
use App\Support\Cache\CacheVersionManager;
use App\Support\Cache\EloquentCache;
use App\Support\Post\VisibilityChecker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Eloquent repository for posts — sole DB access layer for Post.
 *
 * @property-read Post $post
 * @property-read CacheVersionManager $cacheVersions
 * @property-read VisibilityChecker $visibilityChecker
 */
class PostRepository implements PostRepositoryContract
{
    public function __construct(protected Post $post, protected CacheVersionManager $cacheVersions,
        protected VisibilityChecker $visibilityChecker) {}

    /**
     * {@inheritdoc}
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function getPublicListing(?User $viewer): LengthAwarePaginator
    {
        $page = max(1, (int) request()->query('page', 1));
        $viewerKey = $viewer !== null ? $viewer->id : 'guest';
        $cacheKey = sprintf('posts.listing.v2.%s.%s.%s', $this->cacheVersions->get(CacheVersionManager::POSTS),
            $viewerKey, $page);

        /** @var LengthAwarePaginator<int, Post> */
        return EloquentCache::rememberPaginator($cacheKey, now()->addHour(),
            function () use ($viewer): LengthAwarePaginator {
                /** @var Builder<Post> $query */
                $query = $this->post->newQuery();

                return $query->with(['user', 'requiredPermission'])->where('status', PostStatus::Published)
                    ->where(fn (Builder $builder) => $this->visibilityChecker->applyPublicListingScope($builder,
                        $viewer))
                    ->orderByDesc('published_at')->paginate(10);
            });
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function create(AbstractData $data): Post
    {
        /** @var PostData $data */
        $attributes = $data->toArray();
        if ($data->status === PostStatus::Published) {
            $attributes['published_at'] = now();
        }

        return tap($this->post->newInstance(), function (Post $post) use ($attributes): void {
            $post->forceFill($attributes)->saveOrFail();
        });
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function getPost(Post $post): Post
    {
        return $this->findById($post->id);
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function findBySlug(string $slug): Post
    {
        $cacheKey = sprintf('posts.slug.v2.%s.%s', $this->cacheVersions->get(CacheVersionManager::POSTS), $slug);
        /** @var Post $cached */
        $cached = EloquentCache::rememberModel($cacheKey, now()->addHour(),
            fn (): Post => $this->post->newQuery()->with(['user', 'requiredPermission'])->where('slug',
                $slug)->firstOrFail());

        return $cached;
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function findById(int $id): Post
    {
        $cacheKey = sprintf('posts.show.v2.%s.%s', $this->cacheVersions->get(CacheVersionManager::POSTS), $id);
        /** @var Post $cached */
        $cached = EloquentCache::rememberModel($cacheKey, now()->addHour(),
            fn (): Post => $this->post->newQuery()->with(['user', 'requiredPermission'])->where('id',
                $id)->firstOrFail());

        return $cached;
    }

    /**
     * Находит by id or null.

     *
     * @return ?Post
     */
    public function findByIdOrNull(int $id): ?Post
    {
        return $this->post->newQuery()->with(['user', 'requiredPermission'])->where('id', $id)->first();
    }

    /**
     * load author.

     *
     * @return Post
     */
    public function loadAuthor(Post $post): Post
    {
        $post->loadMissing('user');

        return $post;
    }

    /**
     * Обновляет category id.
     */
    public function updateCategoryId(Post $post, int $categoryId): void
    {
        if ($post->category_id === $categoryId) {
            return;
        }
        $post->updateOrFail(['category_id' => $categoryId]);
    }

    /**
     * Возвращает due for scheduled publish.
     */
    /**
     * Возвращает посты для отложенной публикации.
     *
     * @return Collection<int, Post>
     */
    public function getDueForScheduledPublish(): Collection
    {
        return $this->post->newQuery()->where('status', '!=',
            PostStatus::Published)->whereNotNull('scheduled_publish_at')->where('scheduled_publish_at', '<=',
                now())->get();
    }

    /**
     * clear scheduled publish at.

     *
     * @return Post
     */
    public function clearScheduledPublishAt(Post $post): Post
    {
        $post->updateOrFail(['scheduled_publish_at' => null]);

        return $this->refreshWithRelations($post);
    }

    /**
     * count pending moderation.

     *
     * @return int
     */
    public function countPendingModeration(): int
    {
        return $this->post->newQuery()->where('status', PostStatus::PendingModeration)->count();
    }

    /**
     * oldest pending moderation updated at.

     *
     * @return mixed
     */
    public function oldestPendingModerationUpdatedAt(): mixed
    {
        return $this->post->newQuery()->where('status', PostStatus::PendingModeration)->min('updated_at');
    }

    /**
     * {@inheritdoc}
     */
    public function refreshWithRelations(Post $post): Post
    {
        return $post->fresh(['user', 'requiredPermission']) ?? $post;
    }

    /**
     * {@inheritdoc}

     *
     * @return bool
     */
    public function update(AbstractData $data, Post $post): bool
    {
        /** @var PostData $data */
        $attributes = $data->toArray();
        if ($data->status === PostStatus::Published && $post->published_at === null) {
            $attributes['published_at'] = now();
        }

        return $post->forceFill($attributes)->saveOrFail();
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function updateMediaPaths(Post $post, PostMediaData $media): Post
    {
        $post->updateOrFail(['featured_image_path' => $media->featuredImagePath,
            'background_image_path' => $media->backgroundImagePath]);

        return $this->refreshWithRelations($post);
    }

    /**
     * {@inheritdoc}

     *
     * @return ?bool
     */
    public function destroy(Post $post): ?bool
    {
        return $post->deleteOrFail() ?? false;
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function restore(Post $post): Post
    {
        $post->restore();

        return $this->refreshWithRelations($post);
    }

    /**
     * {@inheritdoc}

     *
     * @return bool
     */
    public function isVisibleToViewer(Post $post, ?User $viewer): bool
    {
        $post->loadMissing(['user', 'requiredPermission']);
        if ($viewer !== null) {
            if ($viewer->hasRole(['admin', 'owner'])) {
                return true;
            }
            if ($post->user_id === $viewer->id) {
                return true;
            }
            if ($viewer->can('posts.moderate') && $this->visibilityChecker->isModeratableBy($post, $viewer)) {
                return true;
            }
        }
        if ($post->status !== PostStatus::Published) {
            return false;
        }

        return $this->visibilityChecker->matchesVisibility($post, $viewer);
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function approve(Post $post): Post
    {
        $post->updateOrFail(['status' => PostStatus::Published, 'published_at' => $post->published_at ?? now(),
            'rejection_reason' => null]);

        return $this->refreshWithRelations($post);
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function reject(Post $post, string $reason): Post
    {
        $post->updateOrFail(['status' => PostStatus::Rejected, 'rejection_reason' => $reason]);

        return $this->refreshWithRelations($post);
    }
}
