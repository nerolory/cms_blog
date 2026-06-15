<?php

namespace Tests\Unit\Support;

use App\Support\Database\DatabaseProtection;
use Tests\TestCase;

/**
 * Вспомогательный класс database protection.
 */
class DatabaseProtectionTest extends TestCase
{
    /**
     * test sqlite memory is not protected.
     */
    public function test_sqlite_memory_is_not_protected(): void
    {
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.driver' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        $this->assertFalse(DatabaseProtection::isProtectedConnection('sqlite'));
        $this->assertTrue(DatabaseProtection::testsUseIsolatedDatabase());
    }

    /**
     * test pgsql is protected.
     */
    public function test_pgsql_is_protected(): void
    {
        config(['database.default' => 'pgsql']);
        config(['database.connections.pgsql.driver' => 'pgsql']);
        $this->assertTrue(DatabaseProtection::isProtectedConnection('pgsql'));
        $this->assertFalse(DatabaseProtection::testsUseIsolatedDatabase());
    }

    /**
     * test file sqlite is protected.
     */
    public function test_file_sqlite_is_protected(): void
    {
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.driver' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('testing.sqlite')]);
        $this->assertTrue(DatabaseProtection::isProtectedConnection('sqlite'));
    }
}
