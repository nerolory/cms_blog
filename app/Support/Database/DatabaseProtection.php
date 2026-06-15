<?php

namespace App\Support\Database;

use App\Support\TypeCast;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Guards the primary (non-test) database from destructive Artisan commands and test helpers.
 */
final class DatabaseProtection
{
    /**
     * Whether the connection points at a real dev/prod database (not sqlite :memory:).

     *
     * @return bool
     */
    public static function isProtectedConnection(?string $connection = null): bool
    {
        $connection ??= TypeCast::string(config('database.default'));
        $driver = TypeCast::string(config("database.connections.{$connection}.driver"), $connection);
        if ($driver === 'sqlite') {
            return config("database.connections.{$connection}.database") !== ':memory:';
        }
        /** @var list<string> $protected */
        $protected = config('database.protected_connections', []);

        return in_array($driver, $protected, true);
    }

    /**
     * tests use isolated database.

     *
     * @return bool
     */
    public static function testsUseIsolatedDatabase(): bool
    {
        if (self::allowsPostgresIntegrationTests()) {
            return true;
        }

        return config('database.default') === 'sqlite' && config('database.connections.sqlite.database') === ':memory:';
    }

    /**
     * allows postgres integration tests.

     *
     * @return bool
     */
    public static function allowsPostgresIntegrationTests(): bool
    {
        if (! filter_var(config('database.pg_integration_tests', false), FILTER_VALIDATE_BOOL)) {
            return false;
        }
        $database = config('database.connections.pgsql.database');

        return config('database.default') === 'pgsql' && is_string($database) && str_ends_with($database, '_test');
    }

    /**
     * assert tests use isolated database.
     */
    public static function assertTestsUseIsolatedDatabase(): void
    {
        if (self::testsUseIsolatedDatabase()) {
            return;
        }
        throw new RuntimeException(
            'Tests must use sqlite :memory: or PG integration test database (see phpunit.pgsql.xml). '
            .'Current default connection: '.TypeCast::string(config('database.default'), 'unknown').'. '
            .'RefreshDatabase and migrate:fresh must never run against the dev database.',
        );
    }

    /**
     * register artisan guards.
     */
    public static function registerArtisanGuards(): void
    {
        if (filter_var(config('database.allow_destructive'), FILTER_VALIDATE_BOOL)) {
            return;
        }
        if (app()->runningUnitTests() && self::testsUseIsolatedDatabase()) {
            return;
        }
        DB::prohibitDestructiveCommands(true);
    }
}
