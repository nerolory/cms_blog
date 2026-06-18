<?php

namespace App\Repositories\Contracts;

use App\DTO\SeoData;
use App\Models\Post;
use DateTimeInterface;
use Illuminate\Support\Collection;

/**
 * Контракт репозитория seo.
 */
interface SeoRepositoryContract
{
    /**
     * Обновляет seo.
     *
     * @param  Post  $post  пост
     * @param  SeoData  $data  данные формы

     * @return Post
     */
    public function updateSeo(Post $post, SeoData $data): Post;

    /**
     * Возвращает гостевые посты для sitemap.
     *
     * @return Collection<int, Post>
     */
    public function getPublishedGuestPostsForSitemap(): Collection;

    /**
     * Возвращает latest public listing timestamp.

     *
     * @return ?DateTimeInterface
     */
    public function getLatestPublicListingTimestamp(): ?DateTimeInterface;

    /**
     * Сбрасывает кэш timestamp публичного листинга.
     */
    public function forgetListingTimestampCache(): void;
}
