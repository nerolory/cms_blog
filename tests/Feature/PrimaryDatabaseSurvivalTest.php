<?php

namespace Tests\Feature;

use App\Support\Database\DatabaseProtection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Класс primary database survival.
 */
class PrimaryDatabaseSurvivalTest extends TestCase
{
    /**
     * tear down.
     */
    protected function tearDown(): void
    {
        DB::prohibitDestructiveCommands(false);
        parent::tearDown();
    }

    /**
     * test feature tests use isolated sqlite database.
     */
    public function test_feature_tests_use_isolated_sqlite_database(): void
    {
        $this->assertTrue(DatabaseProtection::testsUseIsolatedDatabase());
    }

    /**
     * test migrate fresh is prohibited on protected default connection.
     */
    public function test_migrate_fresh_is_prohibited_on_protected_default_connection(): void
    {
        config(['database.allow_destructive' => false]);
        config(['database.default' => 'pgsql']);
        config(['database.connections.pgsql.driver' => 'pgsql']);
        DatabaseProtection::registerArtisanGuards();
        $exitCode = Artisan::call('migrate:fresh', ['--force' => true]);
        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('prohibited', Artisan::output());
    }
}
