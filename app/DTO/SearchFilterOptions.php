<?php

namespace App\DTO;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Collection;

/**
 * Справочники фильтров для страницы поиска.

 *
 * @property-read Collection<int, Category> $categories
 * @property-read Collection<int, Tag> $tags
 */
final readonly class SearchFilterOptions
{
    /**
     * @param  Collection<int, Category>  $categories
     * @param  Collection<int, Tag>  $tags
     */
    public function __construct(
        public Collection $categories,
        public Collection $tags,
    ) {}
}
