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
}
