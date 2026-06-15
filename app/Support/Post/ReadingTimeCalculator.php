<?php

namespace App\Support\Post;

/**
 * Вспомогательный класс reading time calculator.
 */
final class ReadingTimeCalculator
{
    private const WORDS_PER_MINUTE = 200;

    /**
     * minutes.

     *
     * @return int
     */
    public static function minutes(string $html): int
    {
        $text = strip_tags($html);
        $words = str_word_count($text);

        return max(1, (int) ceil($words / self::WORDS_PER_MINUTE));
    }
}
