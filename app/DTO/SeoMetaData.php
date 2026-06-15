<?php

namespace App\DTO;

/**
 * DTO seo meta.

 *
 * @property-read string $title
 * @property-read string $description
 * @property-read string $canonicalUrl
 * @property-read string $robots
 * @property-read ?string $ogImageUrl
 * @property-read string $ogType
 * @property-read ?string $publishedAt
 * @property-read ?string $modifiedAt
 * @property-read ?string $authorName
 * @property-read string $pageUrl
 * @property-read string $headline
 */
readonly class SeoMetaData
{
    public function __construct(public string $title, public string $description, public string $canonicalUrl,
        public string $robots, public ?string $ogImageUrl, public string $ogType, public ?string $publishedAt,
        public ?string $modifiedAt, public ?string $authorName, public string $pageUrl, public string $headline) {}

    /**
     * with robots.

     *
     * @return self
     */
    public function withRobots(string $robots): self
    {
        return new self(title: $this->title, description: $this->description, canonicalUrl: $this->canonicalUrl,
            robots: $robots, ogImageUrl: $this->ogImageUrl, ogType: $this->ogType, publishedAt: $this->publishedAt,
            modifiedAt: $this->modifiedAt, authorName: $this->authorName, pageUrl: $this->pageUrl,
            headline: $this->headline);
    }

    /**
     * json ld article.
     *
     * @return array<string, mixed>
     */
    public function jsonLdArticle(): array
    {
        $data = ['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $this->headline,
            'description' => $this->description, 'mainEntityOfPage' => ['@type' => 'WebPage',
                '@id' => $this->canonicalUrl]];
        if ($this->ogImageUrl !== null) {
            $data['image'] = [$this->ogImageUrl];
        }
        if ($this->publishedAt !== null) {
            $data['datePublished'] = $this->publishedAt;
        }
        if ($this->modifiedAt !== null) {
            $data['dateModified'] = $this->modifiedAt;
        }
        if ($this->authorName !== null) {
            $data['author'] = ['@type' => 'Person', 'name' => $this->authorName];
        }

        return $data;
    }
}
