<?php

namespace App\DTO;

use Illuminate\Support\Collection;

/**
 * Результат извлечения оглавления: записи и HTML с id заголовков.

 *
 * @property-read Collection<int, TableOfContentsEntry> $entries
 * @property-read string $html
 */
readonly class TableOfContentsResult
{
    /**
     * @param  Collection<int, TableOfContentsEntry>  $entries
     */
    public function __construct(
        public Collection $entries,
        public string $html,
    ) {}
}
