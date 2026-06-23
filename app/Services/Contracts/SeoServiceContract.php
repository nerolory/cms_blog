<?php

namespace App\Services\Contracts;

use App\DTO\HttpCacheContext;
use App\DTO\PostListEngagementItem;
use App\DTO\SeoData;
use App\DTO\SeoMetaData;
use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Контракт сервиса seo.
 */
interface SeoServiceContract
{
    /**
     * resolve for post.
     *
     * @param  Post  $post  пост

     * @return SeoMetaData
     */
    public function resolveForPost(Post $post): SeoMetaData;

    /**
     * resolve for preview.
     *
     * @param  Post  $post  пост

     * @return SeoMetaData
     */
    public function resolveForPreview(Post $post): SeoMetaData;

    /**
     * Обновляет for post.
     *
     * @param  Post  $post  пост
     * @param  SeoData  $data  данные формы

     * @return Post
     */
    public function updateForPost(Post $post, SeoData $data): Post;

    /**
     * Обновляет from filament.
     *
     * @param  Post  $post  пост
     * @param  SeoData  $data  данные формы

     * @return Post
     */
    public function updateFromFilament(Post $post, SeoData $data): Post;

    /**
     * build sitemap xml.

     *
     * @return string
     */
    public function buildSitemapXml(): string;

    /**
     * build robots txt.

     *
     * @return string
     */
    public function buildRobotsTxt(): string;

    /**
     * http cache context for post.
     *
     * @param  Post  $post  пост
     * @param  int  $viewsCount  count
     * @param  string  $engagementVersion  версия engagement для ETag гостя
     * @param  int  $totalVisibleComments  число видимых комментариев для ETag гостя

     * @return HttpCacheContext
     */
    public function httpCacheContextForPost(
        Post $post,
        ?User $viewer,
        int $viewsCount = 0,
        string $engagementVersion = '0',
        int $totalVisibleComments = 0,
    ): HttpCacheContext;

    /**
     * Формирует HTTP-кеш контекст для листинга постов.
     *
     * @param  LengthAwarePaginator<int, Post>  $posts
     * @param  Collection<int, PostListEngagementItem>|null  $listingEngagement
     * @return HttpCacheContext
     */
    public function httpCacheContextForListing(
        LengthAwarePaginator $posts,
        ?User $viewer,
        ?Collection $listingEngagement = null,
    ): HttpCacheContext;
}
