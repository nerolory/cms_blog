<?php

namespace Tests\Feature;

use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

/**
 * Класс refresh database safety.
 */
class RefreshDatabaseSafetyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * test project refresh database trait requires sqlite memory.
     */
    public function test_project_refresh_database_trait_requires_sqlite_memory(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }
}
