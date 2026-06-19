<?php

namespace App\Support\Formatting;

/**
 * Сокращённое отображение больших целых чисел (1K, 1.5M, 1B).
 */
final class CompactNumberFormatter
{
    private const int COMPACT_FROM = 10_000;

    /**
     * Форматирует число: до порога — с разделителями тысяч, выше
     * — K/M/B.

     *
     * @return string
     */
    public static function format(int $value): string
    {
        if ($value < self::COMPACT_FROM) {
            return number_format($value);
        }

        if ($value >= 1_000_000_000) {
            return self::formatWithSuffix($value, 1_000_000_000, 'B');
        }

        if ($value >= 1_000_000) {
            return self::formatWithSuffix($value, 1_000_000, 'M');
        }

        return self::formatWithSuffix($value, 1_000, 'K');
    }

    private static function formatWithSuffix(int $value, int $divisor, string $suffix): string
    {
        if ($value % $divisor === 0) {
            return (string) intdiv($value, $divisor).$suffix;
        }

        $scaled = $value / $divisor;
        $formatted = rtrim(rtrim(number_format($scaled, 1, '.', ''), '0'), '.');

        return $formatted.$suffix;
    }
}
