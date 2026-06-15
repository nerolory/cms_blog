<?php

namespace App\Support\Feed;

use App\DTO\FeedEntryData;
use Illuminate\Support\Collection;

/**
 * Общая сборка XML-строк для RSS и Atom.
 */
final class FeedXmlBuilder
{
    /**
     * XML-фрагмент RSS &lt;item&gt; для одной записи.
     *
     * @return string XML-фрагмент item
     */
    public function rssItem(FeedEntryData $entry): string
    {
        $post = $entry->post;
        $lines = ['<item>', '<title>'.$this->xmlEscape($post->title).'</title>',
            '<link>'.$this->xmlEscape($entry->url).'</link>',
            '<guid isPermaLink="true">'.$this->xmlEscape($entry->url).'</guid>',
            '<description>'.$this->xmlEscape($entry->description).'</description>'];
        if ($post->published_at !== null) {
            $lines[] = '<pubDate>'.$post->published_at->toRfc2822String().'</pubDate>';
        }
        $lines[] = '</item>';

        return $this->joinLinesArray($lines);
    }

    /**
     * XML-фрагмент Atom &lt;entry&gt; для одной записи.
     *
     * @return string XML-фрагмент entry
     */
    public function atomEntry(FeedEntryData $entry): string
    {
        $post = $entry->post;
        $lines = ['<entry>', '<title>'.$this->xmlEscape($post->title).'</title>',
            '<link href="'.$this->xmlEscape($entry->url).'"/>', '<id>'.$this->xmlEscape($entry->url).'</id>',
            '<summary>'.$this->xmlEscape($entry->description).'</summary>'];
        if ($post->published_at !== null) {
            $lines[] = '<published>'.$post->published_at->toAtomString().'</published>';
            $lines[] = '<updated>'.$post->published_at->toAtomString().'</updated>';
        }
        $lines[] = '</entry>';

        return $this->joinLinesArray($lines);
    }

    /**
     * Экранирование для XML 1.0.
     *
     * @return string экранированная строка
     */
    public function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1);
    }

    /**
     * Склеивает строки XML-документа в итоговый текст.
     *
     * @param  Collection<int, string>  $lines
     * @return string готовый XML с завершающим переводом строки
     */
    public function joinLines(Collection $lines): string
    {
        return $lines->implode("\n")."\n";
    }

    /**
     * @param  list<string>  $lines
     */
    private function joinLinesArray(array $lines): string
    {
        return $this->joinLines(collect($lines));
    }
}
