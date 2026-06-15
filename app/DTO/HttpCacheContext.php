<?php

namespace App\DTO;

use DateTimeInterface;

/**
 * DTO http cache context.

 *
 * @property-read DateTimeInterface $lastModified
 * @property-read string $etag
 * @property-read string $cacheControl
 * @property-read ?string $robotsTag
 */
readonly class HttpCacheContext
{
    public function __construct(public DateTimeInterface $lastModified, public string $etag,
        public string $cacheControl, public ?string $robotsTag) {}
}
