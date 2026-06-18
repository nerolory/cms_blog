<?php

namespace Tests\Unit\Support;

use App\Support\Formatting\CompactNumberFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Сокращённое форматирование чисел.
 */
class CompactNumberFormatterTest extends TestCase
{
    #[DataProvider('compactFormatProvider')]
    public function test_format(int $value, string $expected): void
    {
        $this->assertSame($expected, CompactNumberFormatter::format($value));
    }

    /**
     * @return list<array{0: int, 1: string}>
     */
    public static function compactFormatProvider(): array
    {
        return [
            [42, '42'],
            [9_999, '9,999'],
            [10_000, '10K'],
            [100_000, '100K'],
            [1_500_000, '1.5M'],
            [1_000_000, '1M'],
            [1_000_000_000, '1B'],
        ];
    }
}
