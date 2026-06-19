<?php

namespace App\Repositories;

use App\DTO\SeoData;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryContract;
use App\Repositories\Contracts\SeoRepositoryContract;
use App\Support\Cache\ApplicationCacheKeys;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Репозиторий seo.
 *
 * @property-read Post $post
 * @property-read PostRepositoryContract $posts
 */
class SeoRepository implements SeoRepositoryContract
{
    private const LISTING_TIMESTAMP_CACHE_TTL_SECONDS = 3600;

    public function __construct(protected Post $post, protected PostRepositoryContract $posts) {}

    /**
     * Обновляет seo.

     *
     * @return Post
     */
    public function updateSeo(Post $post, SeoData $data): Post
    {
        $post->updateOrFail($data->toArray());

        return $this->posts->refreshWithRelations($post);
    }

    /**
     * Возвращает гостевые посты для sitemap.
     *
     * @return Collection<int, Post>
     */
    public function getPublishedGuestPostsForSitemap(): Collection
    {
        return $this->post->newQuery()->with('user')->where('status', PostStatus::Published)->where('visibility',
            PostVisibility::Guest->value)->orderByDesc('published_at')->get();
    }

    /**
     * Возвращает latest public listing timestamp.

     *
     * @return ?DateTimeInterface
     */
    public function getLatestPublicListingTimestamp(): ?DateTimeInterface
    {
        /** @var ?int $timestamp */
        $timestamp = Cache::remember(
            ApplicationCacheKeys::SEO_LISTING_LATEST_UPDATED_AT,
            self::LISTING_TIMESTAMP_CACHE_TTL_SECONDS,
            function (): ?int {
                /** @var Post|null $latest */
                $latest = $this->post->newQuery()->where('status', PostStatus::Published)->where('visibility',
                    PostVisibility::Guest->value)->orderByDesc('updated_at')->first(['updated_at']);

                return $latest?->updated_at?->getTimestamp();
            },
        );

        if ($timestamp === null) {
            return null;
        }

        return (new \DateTimeImmutable)->setTimestamp($timestamp);
    }

    /**
     * {@inheritdoc}
     */
    public function forgetListingTimestampCache(): void
    {
        Cache::forget(ApplicationCacheKeys::SEO_LISTING_LATEST_UPDATED_AT);
    }
}
