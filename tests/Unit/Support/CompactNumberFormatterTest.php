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
    /**
     * test format.
     *
     * @param  int  $value
     * @param  string  $expected
     */
    #[DataProvider('compactFormatProvider')]
    public function test_format(int $value, string $expected): void
    {
        $this->assertSame($expected, CompactNumberFormatter::format($value));
    }

    /**
     * Поставщик данных для data provider.
     *
     * @return array<string, mixed>
     */
    public static function compactFormatProvider(): array
    {
        return [
            'small' => [42, '42'],
            'below_threshold' => [9_999, '9,999'],
            'ten_k' => [10_000, '10K'],
            'hundred_k' => [100_000, '100K'],
            'one_point_five_m' => [1_500_000, '1.5M'],
            'one_m' => [1_000_000, '1M'],
            'one_b' => [1_000_000_000, '1B'],
        ];
    }
}
