<?php

namespace App\Support\Post;

use App\DTO\TableOfContentsEntry;
use App\DTO\TableOfContentsResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Извлечение оглавления из HTML тела поста и проставление id
 * заголовкам.
 */
final class TableOfContentsExtractor
{
    /**
     * extract and inject ids.

     *
     * @return TableOfContentsResult
     */
    public static function extractAndInjectIds(string $html): TableOfContentsResult
    {
        if (! preg_match_all('/<h([2-4])([^>]*)>(.*?)<\/h\1>/is', $html, $matches, PREG_SET_ORDER)) {
            return new TableOfContentsResult(entries: collect(), html: $html);
        }
        $entries = collect();
        $index = 0;
        foreach ($matches as $match) {
            $level = (int) $match[1];
            $text = trim(strip_tags($match[3]));
            if ($text === '') {
                continue;
            }
            $index++;
            $id = 'toc-'.$index.'-'.Str::slug($text);
            $entries->push(new TableOfContentsEntry(id: $id, text: $text, level: $level));
        }
        if ($entries->isEmpty()) {
            return new TableOfContentsResult(entries: $entries, html: $html);
        }
        $entryIndex = 0;
        $html = preg_replace_callback('/<h([2-4])([^>]*)>(.*?)<\/h\1>/is', function (array $match) use ($entries,
            &$entryIndex): string {
            $entry = $entries->get($entryIndex);
            if ($entry === null) {
                return $match[0];
            }
            $entryIndex++;
            $attrs = $match[2];
            if (str_contains($attrs, 'id=')) {
                return $match[0];
            }

            return '<h'.$match[1].$attrs.' id="'.$entry->id.'">'.$match[3].'</h'.$match[1].'>';
        }, $html) ?? $html;

        return new TableOfContentsResult(entries: $entries, html: $html);
    }

    /**
     * extract.
     *
     * @return Collection<int, TableOfContentsEntry>
     */
    public static function extract(string $html): Collection
    {
        return self::extractAndInjectIds($html)->entries;
    }

    /**
     * inject ids.

     *
     * @return string
     */
    public static function injectIds(string $html): string
    {
        return self::extractAndInjectIds($html)->html;
    }
}
