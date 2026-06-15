<?php

namespace Tests\Feature;

use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

/**
 * Класс database connection guard.
 */
class DatabaseConnectionGuardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * test uses sqlite in memory not pgsql.
     */
    public function test_uses_sqlite_in_memory_not_pgsql(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }
}
