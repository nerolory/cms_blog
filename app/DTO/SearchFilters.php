<?php

namespace App\DTO;

use Illuminate\Support\Collection;

/**
 * DTO search filters.
 *
 * @property-read string $query
 * @property-read ?int $categoryId
 * @property-read Collection<int, int> $tagIds
 * @property-read ?int $authorId
 * @property-read ?string $dateFrom
 * @property-read ?string $dateTo
 * @property-read int $page
 * @property-read int $perPage
 */
readonly class SearchFilters
{
    /**
     * @param  Collection<int, int>  $tagIds
     */
    public function __construct(public string $query, public ?int $categoryId = null,
        public Collection $tagIds = new Collection, public ?int $authorId = null, public ?string $dateFrom = null,
        public ?string $dateTo = null, public int $page = 1, public int $perPage = 15) {}

    /**
     * from.
     *
     * @param  ?int  $categoryId  id
     * @param  Collection<int, int>  $tagIds  ids
     * @param  ?int  $authorId  id
     * @param  ?string  $dateFrom  from
     * @param  ?string  $dateTo  to

     * @return self
     */
    public static function fromRequest(string $query, ?int $categoryId, Collection $tagIds, ?int $authorId,
        ?string $dateFrom, ?string $dateTo, int $page): self
    {
        return new self(query: trim($query), categoryId: $categoryId, tagIds: $tagIds, authorId: $authorId,
            dateFrom: $dateFrom, dateTo: $dateTo, page: max(1, $page));
    }

    /**
     * cache key suffix.

     *
     * @return string
     */
    public function cacheKeySuffix(): string
    {
        $parts = [$this->query, (string) ($this->categoryId ?? ''), $this->tagIds->sort()->implode(','),
            (string) ($this->authorId ?? ''), $this->dateFrom ?? '', $this->dateTo ?? '', (string) $this->page];

        return hash('sha256', implode('|', $parts));
    }

    /**
     * Проверяет наличие query.

     *
     * @return bool
     */
    public function hasQuery(): bool
    {
        return $this->query !== '';
    }
}
