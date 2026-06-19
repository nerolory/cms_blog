<?php

namespace App\Services;

use App\DTO\HttpCacheContext;
use App\DTO\PostListEngagementItem;
use App\DTO\SeoData;
use App\DTO\SeoMetaData;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\FileRepositoryContract;
use App\Repositories\Contracts\PostRepositoryContract;
use App\Repositories\Contracts\SeoRepositoryContract;
use App\Services\Contracts\SeoServiceContract;
use App\Support\Cache\CacheVersionManager;
use App\Support\TypeCast;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Сервис seo.
 *
 * @property-read SeoRepositoryContract $seoRepository
 * @property-read FileRepositoryContract $files
 * @property-read CacheVersionManager $cacheVersions
 * @property-read PostRepositoryContract $posts
 */
class SeoService implements SeoServiceContract
{
    public function __construct(protected SeoRepositoryContract $seoRepository, protected FileRepositoryContract $files,
        protected CacheVersionManager $cacheVersions, protected PostRepositoryContract $posts) {}

    /**
     * resolve for post.
     *
     * @param  Post  $post  пост

     * @return SeoMetaData
     */
    public function resolveForPost(Post $post): SeoMetaData
    {
        $post = $this->posts->loadAuthor($post);
        $title = $this->resolveTitle($post);
        $description = $this->resolveDescription($post);
        $pageUrl = route('posts.show', $post);
        $canonicalUrl = $this->resolveCanonicalUrl($post, $pageUrl);
        $robots = $this->resolveRobots($post);
        $ogImageUrl = $this->resolveOgImageUrl($post);

        return new SeoMetaData(title: $title, description: $description, canonicalUrl: $canonicalUrl, robots: $robots,
            ogImageUrl: $ogImageUrl, ogType: 'article', publishedAt: $post->published_at?->toIso8601String(),
            modifiedAt: $post->updated_at?->toIso8601String(), authorName: $post->user?->name, pageUrl: $pageUrl,
            headline: $post->title);
    }

    /**
     * resolve for preview.
     *
     * @param  Post  $post  пост

     * @return SeoMetaData
     */
    public function resolveForPreview(Post $post): SeoMetaData
    {
        return $this->resolveForPost($post)->withRobots('noindex,nofollow');
    }

    /**
     * Обновляет for post.
     *
     * @param  Post  $post  пост
     * @param  SeoData  $data  данные формы

     * @return Post
     */
    public function updateForPost(Post $post, SeoData $data): Post
    {
        return $this->seoRepository->updateSeo($post, $data);
    }

    /**
     * Обновляет from filament.
     *
     * @param  Post  $post  пост
     * @param  SeoData  $data  данные формы

     * @return Post
     */
    public function updateFromFilament(Post $post, SeoData $data): Post
    {
        return $this->updateForPost($post, $data);
    }

    /**
     * build sitemap xml.

     *
     * @return string
     */
    public function buildSitemapXml(): string
    {
        $cacheKey = sprintf('seo.sitemap.%s', $this->cacheVersions->get(CacheVersionManager::POSTS));

        return Cache::remember($cacheKey, now()->addHour(), fn (): string => $this->renderSitemapXml());
    }

    private function renderSitemapXml(): string
    {
        $posts = $this->seoRepository->getPublishedGuestPostsForSitemap();
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
        $lines[] = $this->sitemapUrlEntry(url('/'), now(), 'daily', '1.0');
        $lines[] = $this->sitemapUrlEntry(route('posts.index'), now(), 'hourly', '0.8');
        foreach ($posts as $post) {
            if (! $post instanceof Post) {
                continue;
            }
            $lastMod = $post->updated_at ?? $post->published_at ?? now();
            $lines[] = $this->sitemapUrlEntry(route('posts.show', $post), $lastMod, 'weekly', '0.7');
        }
        $lines[] = '</urlset>';

        return implode("\n", $lines)."\n";
    }

    /**
     * build robots txt.

     *
     * @return string
     */
    public function buildRobotsTxt(): string
    {
        $lines = ['User-agent: *', 'Allow: /'];
        /** @var list<string> $disallow */
        $disallow = config('seo.robots_txt.disallow', []);
        foreach ($disallow as $path) {
            $lines[] = 'Disallow: '.$path;
        }
        $lines[] = 'Sitemap: '.url('sitemap.xml');

        return implode("\n", $lines)."\n";
    }

    /**
     * http cache context for post.
     *
     * @param  Post  $post  пост
     * @param  int  $viewsCount  count

     * @return HttpCacheContext
     */
    public function httpCacheContextForPost(
        Post $post,
        ?User $viewer,
        int $viewsCount = 0,
        string $engagementVersion = '0',
        int $totalVisibleComments = 0,
    ): HttpCacheContext {
        $lastModified = $post->updated_at ?? now();
        $viewerKey = $viewer !== null ? (string) $viewer->id : 'guest';
        $etag = $this->buildEtag([$this->cacheVersions->current(),
            $this->cacheVersions->get(CacheVersionManager::POSTS),
            'post-v3', (string) $post->id, (string) $lastModified->getTimestamp(), $viewerKey,
            (string) $viewsCount, $engagementVersion, (string) $totalVisibleComments]);

        return new HttpCacheContext(lastModified: $lastModified, etag: $etag,
            cacheControl: 'private, max-age=60, must-revalidate', robotsTag: $this->resolveRobots($post));
    }

    /**
     * http cache context for listing.
     *
     * @param  LengthAwarePaginator<int, Post>  $posts
     * @return HttpCacheContext
     */
    public function httpCacheContextForListing(
        LengthAwarePaginator $posts,
        ?User $viewer,
        ?Collection $listingEngagement = null,
    ): HttpCacheContext {
        $viewerKey = $viewer !== null ? (string) $viewer->id : 'guest';
        $latest = $posts->getCollection()->max(fn (Post $post): int => ($post->updated_at ?? now())
            ->getTimestamp()) ?? now()->getTimestamp();
        $lastModified = (new \DateTimeImmutable)->setTimestamp((int) $latest);
        $viewsFingerprint = $this->buildListingViewsFingerprint($listingEngagement);
        $etag = $this->buildEtag([$this->cacheVersions->current(),
            $this->cacheVersions->get(CacheVersionManager::POSTS),
            'listing-v4', (string) $posts->currentPage(), (string) $posts->total(),
            (string) $lastModified->getTimestamp(), $viewerKey, $viewsFingerprint]);
        $repositoryTimestamp = $this->seoRepository->getLatestPublicListingTimestamp();
        if ($repositoryTimestamp !== null && $repositoryTimestamp->getTimestamp() > $lastModified->getTimestamp()) {
            $lastModified = $repositoryTimestamp;
        }

        return new HttpCacheContext(lastModified: $lastModified, etag: $etag, cacheControl: $this->cacheControlHeader(),
            robotsTag: TypeCast::string(config('seo.defaults.robots', 'index,follow'), 'index,follow'));
    }

    private function resolveTitle(Post $post): string
    {
        $custom = TypeCast::nullableString($post->meta_title);

        return $custom ?? $post->title;
    }

    private function resolveDescription(Post $post): string
    {
        $custom = TypeCast::nullableString($post->meta_description);
        if ($custom !== null) {
            return $custom;
        }
        $excerpt = trim(TypeCast::string($post->excerpt ?? ''));
        if ($excerpt !== '') {
            return Str::limit($excerpt, TypeCast::int(config('seo.description_max_length', 160), 160));
        }
        $plain = $this->plainTextFromHtml($post->body);

        return Str::limit($plain, TypeCast::int(config('seo.description_max_length', 160), 160));
    }

    private function resolveCanonicalUrl(Post $post, string $fallback): string
    {
        $custom = TypeCast::nullableString($post->canonical_url);

        return $custom ?? $fallback;
    }

    private function resolveRobots(Post $post): string
    {
        $custom = TypeCast::nullableString($post->robots);
        if ($custom !== null) {
            return $custom;
        }
        if ($this->isPubliclyIndexable($post)) {
            return TypeCast::string(config('seo.defaults.robots', 'index,follow'), 'index,follow');
        }

        return TypeCast::string(config('seo.defaults.private_robots', 'noindex,nofollow'), 'noindex,nofollow');
    }

    private function isPubliclyIndexable(Post $post): bool
    {
        return $post->status === PostStatus::Published && $post->visibility === PostVisibility::Guest->value;
    }

    private function resolveOgImageUrl(Post $post): ?string
    {
        $path = TypeCast::nullableString($post->og_image_path) ?? TypeCast::nullableString($post->featured_image_path);
        if ($path === null) {
            return null;
        }
        $url = $this->files->publicUrl($path, $this->cacheVersions->mediaVersionFor($path));
        if ($url === null) {
            return null;
        }

        return url($url);
    }

    /**
     * @param  list<string>  $parts
     */
    private function buildEtag(array $parts): string
    {
        return '"'.hash('sha256', implode('|', $parts)).'"';
    }

    private function cacheControlHeader(): string
    {
        $maxAge = TypeCast::int(config('seo.cache.max_age', 3600), 3600);

        return 'private, max-age='.$maxAge.', must-revalidate';
    }

    /**
     * @param  Collection<int, PostListEngagementItem>|null  $listingEngagement
     */
    private function buildListingViewsFingerprint(?Collection $listingEngagement): string
    {
        if ($listingEngagement === null || $listingEngagement->isEmpty()) {
            return '';
        }

        return $listingEngagement
            ->sortKeys()
            ->map(static fn (PostListEngagementItem $item,
                mixed $postId): string => TypeCast::int($postId).':'.$item->viewsCount)
            ->implode(',');
    }

    private function plainTextFromHtml(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalized = preg_replace('/\s+/u', ' ', trim($text));

        return $normalized ?? trim($text);
    }

    private function sitemapUrlEntry(string $loc, \DateTimeInterface $lastMod, string $changefreq,
        string $priority): string
    {
        return sprintf(
            '  <url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%s</priority></url>',
            htmlspecialchars($loc, ENT_XML1),
            $lastMod->format('Y-m-d'),
            $changefreq,
            $priority,
        );
    }
}
