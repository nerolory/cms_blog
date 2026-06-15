<?php

namespace Tests\Unit\Support;

use App\Support\TypeCast;
use Tests\TestCase;

/**
 * Вспомогательный класс type cast.
 */
class TypeCastTest extends TestCase
{
    /**
     * test trim to null returns null for blank strings.
     */
    public function test_trim_to_null_returns_null_for_blank_strings(): void
    {
        $this->assertNull(TypeCast::trimToNull(null));
        $this->assertNull(TypeCast::trimToNull(''));
        $this->assertNull(TypeCast::trimToNull('   '));
    }

    /**
     * test trim or keep returns fallback for blank strings.
     */
    public function test_trim_or_keep_returns_fallback_for_blank_strings(): void
    {
        $this->assertSame('existing', TypeCast::trimOrKeep('', 'existing'));
        $this->assertSame('existing', TypeCast::trimOrKeep('   ', 'existing'));
        $this->assertSame('new-value', TypeCast::trimOrKeep('  new-value  ', 'existing'));
    }
}
