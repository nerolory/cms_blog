<?php

namespace App\Support;

use App\DTO\PostThemeValues;

/**
 * Normalizes per-post theme values for storage and rendering.
 */
final class PostTheme
{
    public const MIN_CONTENT_OPACITY = 50;

    public const MAX_CONTENT_OPACITY = 100;

    /**
     * normalize.

     *
     * @return PostThemeValues
     */
    public static function normalize(?string $primary, ?string $accent, mixed $opacity): PostThemeValues
    {
        return new PostThemeValues(primaryColor: self::normalizeColor($primary),
            accentColor: self::normalizeColor($accent), contentOpacity: self::normalizeOpacity($opacity));
    }

    /**
     * normalize color.

     *
     * @return ?string
     */
    public static function normalizeColor(?string $color): ?string
    {
        if ($color === null) {
            return null;
        }
        $color = strtoupper(trim($color));
        if ($color === '') {
            return null;
        }
        if (! preg_match('/^#[0-9A-F]{6}$/', $color)) {
            return null;
        }

        return $color;
    }

    /**
     * normalize opacity.

     *
     * @return int
     */
    public static function normalizeOpacity(mixed $opacity): int
    {
        $value = is_numeric($opacity) ? (int) $opacity : self::MAX_CONTENT_OPACITY;

        return max(self::MIN_CONTENT_OPACITY, min(self::MAX_CONTENT_OPACITY, $value));
    }

    /**
     * Цвет подложки контента: контраст к цвету текста темы.

     *
     * @return string
     */
    public static function contrastSurface(?string $textColor): string
    {
        if ($textColor === null) {
            return '#ffffff';
        }

        return self::relativeLuminance($textColor) < 0.45 ? '#ffffff' : '#1a1a1a';
    }

    /**
     * Однотонный текст на подложке: противоположная яркость к
     * цвету поверхности.

     *
     * @return string
     */
    public static function contrastingText(?string $surfaceColor): string
    {
        if ($surfaceColor === null) {
            return '';
        }

        return self::relativeLuminance($surfaceColor) < 0.45 ? '#f5f5f5' : '#1a1a1a';
    }

    /**
     * Относительная яркость hex-цвета (0–1).

     *
     * @return float
     */
    public static function relativeLuminance(string $hexColor): float
    {
        $hex = ltrim(strtoupper($hexColor), '#');
        if (strlen($hex) !== 6) {
            return 0.5;
        }
        $red = hexdec(substr($hex, 0, 2)) / 255;
        $green = hexdec(substr($hex, 2, 2)) / 255;
        $blue = hexdec(substr($hex, 4, 2)) / 255;

        return 0.2126 * $red + 0.7152 * $green + 0.0722 * $blue;
    }
}
