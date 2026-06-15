<?php

namespace App\DTO;

use App\Models\Post;

/**
 * DTO одной записи RSS/Atom-ленты.
 *
 * @property-read Post $post
 * @property-read string $description
 * @property-read string $url
 */
readonly class FeedEntryData
{
    public function __construct(public Post $post, public string $description, public string $url) {}
}
