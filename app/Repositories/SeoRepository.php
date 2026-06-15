<?php

namespace App\Repositories;

use App\DTO\SeoData;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryContract;
use App\Repositories\Contracts\SeoRepositoryContract;
use DateTimeInterface;
use Illuminate\Support\Collection;

/**
 * Репозиторий seo.
 *
 * @property-read Post $post
 * @property-read PostRepositoryContract $posts
 */
class SeoRepository implements SeoRepositoryContract
{
    public function __construct(protected Post $post, protected PostRepositoryContract $posts) {}

    /**
     * Обновляет seo.
     *
     * @param  Post  $post  пост
     * @param  SeoData  $data  данные формы

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
        /** @var Post|null $latest */
        $latest = $this->post->newQuery()->where('status', PostStatus::Published)->where('visibility',
            PostVisibility::Guest->value)->orderByDesc('updated_at')->first(['updated_at']);

        return $latest?->updated_at;
    }
}
