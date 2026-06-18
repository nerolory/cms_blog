<?php

namespace App\Services;

use App\DTO\FeedEntryData;
use App\Models\Post;
use App\Repositories\Contracts\FeedRepositoryContract;
use App\Services\Contracts\FeedServiceContract;
use App\Services\Contracts\SeoServiceContract;
use App\Services\Contracts\SiteSettingsServiceContract;
use App\Support\Feed\FeedXmlBuilder;
use App\Support\TypeCast;
use Illuminate\Support\Collection;

/**
 * Сервис feed.
 *
 * @property-read FeedRepositoryContract $feedRepository
 * @property-read SeoServiceContract $seoService
 * @property-read FeedXmlBuilder $xmlBuilder
 * @property-read SiteSettingsServiceContract $siteSettings
 */
class FeedService implements FeedServiceContract
{
    public function __construct(
        protected FeedRepositoryContract $feedRepository,
        protected SeoServiceContract $seoService,
        protected FeedXmlBuilder $xmlBuilder,
        protected SiteSettingsServiceContract $siteSettings,
    ) {}

    /**
     * build rss.

     *
     * @return string
     */
    public function buildRss(): string
    {
        $siteName = $this->siteSettings->siteName();
        /** @var Collection<int, string> $lines */
        $lines = collect(['<?xml version="1.0" encoding="UTF-8"?>',
            '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">', '<channel>',
            '<title>'.$this->xmlBuilder->xmlEscape($siteName).'</title>',
            '<link>'.$this->xmlBuilder->xmlEscape(url('/')).'</link>',
            '<description>'.$this->xmlBuilder->xmlEscape($siteName).'</description>',
            '<atom:link href="'.$this->xmlBuilder->xmlEscape(route('feed.rss')).'" rel="self" '
            .'type="application/rss+xml"/>']);
        foreach ($this->resolvedFeedEntries() as $entry) {
            $lines->push($this->xmlBuilder->rssItem($entry));
        }
        $lines->push('</channel>', '</rss>');

        return $this->xmlBuilder->joinLines($lines);
    }

    /**
     * build atom.

     *
     * @return string
     */
    public function buildAtom(): string
    {
        $entries = $this->resolvedFeedEntries();
        $firstPost = $entries->first()?->post;
        $updated = $firstPost instanceof Post && $firstPost->published_at !== null
            ? $firstPost->published_at
            : now();
        $siteName = $this->siteSettings->siteName();
        /** @var Collection<int, string> $lines */
        $lines = collect(['<?xml version="1.0" encoding="UTF-8"?>', '<feed xmlns="http://www.w3.org/2005/Atom">',
            '<title>'.$this->xmlBuilder->xmlEscape($siteName).'</title>',
            '<link href="'.$this->xmlBuilder->xmlEscape(url('/')).'"/>',
            '<link href="'.$this->xmlBuilder->xmlEscape(route('feed.atom')).'" rel="self"/>',
            '<id>'.$this->xmlBuilder->xmlEscape(url('/')).'</id>', '<updated>'.$updated->toAtomString().'</updated>']);
        foreach ($entries as $entry) {
            $lines->push($this->xmlBuilder->atomEntry($entry));
        }
        $lines->push('</feed>');

        return $this->xmlBuilder->joinLines($lines);
    }

    /**
     * @return Collection<int, FeedEntryData>
     */
    private function resolvedFeedEntries(): Collection
    {
        return $this->feedRepository->getLatestPublished($this->feedLimit())
            ->map(function (Post $post): FeedEntryData {
                $seo = $this->seoService->resolveForPost($post);

                return new FeedEntryData($post, $seo->description, route('posts.show', $post));
            });
    }

    private function feedLimit(): int
    {
        return TypeCast::int(config('feed.limit', 20), 20);
    }
}
