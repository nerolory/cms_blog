<?php

namespace App\DTO;

use App\Models\Post;

/**
 * DTO search result item.

 *
 * @property-read Post $post
 * @property-read ?string $highlightTitle
 * @property-read ?string $highlightExcerpt
 * @property-read float $rank
 */
readonly class SearchResultItem
{
    public function __construct(public Post $post, public ?string $highlightTitle = null,
        public ?string $highlightExcerpt = null, public float $rank = 0.0) {}
}
