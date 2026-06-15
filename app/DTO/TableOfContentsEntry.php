<?php

namespace App\DTO;

/**
 * DTO table of contents entry.

 *
 * @property-read string $id
 * @property-read string $text
 * @property-read int $level
 */
readonly class TableOfContentsEntry
{
    public function __construct(public string $id, public string $text, public int $level) {}
}
