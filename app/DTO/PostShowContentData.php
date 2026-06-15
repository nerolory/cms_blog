<?php

namespace App\DTO;

use Illuminate\Support\Collection;

/**
 * Данные тела поста: оглавление, HTML с якорями, время чтения.
 *
 * @property-read Collection<int, TableOfContentsEntry> $toc
 * @property-read string $bodyWithToc
 * @property-read int $readingMinutes
 */
readonly class PostShowContentData
{
    /**
     * @param  Collection<int, TableOfContentsEntry>  $toc
     */
    public function __construct(public Collection $toc, public string $bodyWithToc, public int $readingMinutes) {}

    /**
     * Переменные для view pages.posts.show.
     *
     * @return array{toc: Collection<int, TableOfContentsEntry>, bodyWithToc: string, readingMinutes: int}
     */
    public function toViewVariables(): array
    {
        return ['toc' => $this->toc, 'bodyWithToc' => $this->bodyWithToc, 'readingMinutes' => $this->readingMinutes];
    }
}
