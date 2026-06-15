<?php

namespace App\Support;

/**
 * Safe casts from mixed for DTO boundaries (PHPStan level 9).
 */
final class TypeCast
{
    /**
     * string.

     *
     * @return string
     */
    public static function string(mixed $value, string $default = ''): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $default;
    }

    /**
     * nullable string.

     *
     * @return ?string
     */
    public static function nullableString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::string($value);
    }

    /**
     * int.

     *
     * @return int
     */
    public static function int(mixed $value, int $default = 0): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    /**
     * nullable int.

     *
     * @return ?int
     */
    public static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::int($value);
    }

    /**
     * bool.

     *
     * @return bool
     */
    public static function bool(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? (bool) $value;
    }

    /**
     * array.
     *
     * @return array<string, mixed>
     */
    public static function array(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * trim required.

     *
     * @return string
     */
    public static function trimRequired(mixed $value): string
    {
        return trim(self::string($value));
    }

    /**
     * trim to null.

     *
     * @return ?string
     */
    public static function trimToNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim(self::string($value));

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * trim or keep.

     *
     * @return string
     */
    public static function trimOrKeep(mixed $value, string $fallback): string
    {
        return self::trimToNull($value) ?? $fallback;
    }
}
