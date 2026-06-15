<?php

namespace Tests\Integration;

use App\Support\Database\DatabaseProtection;
use App\Support\TypeCast;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PDO;
use PDOException;
use Tests\Concerns\SeedsRoles;

/**
 * Класс postgres test case.
 */
abstract class PostgresTestCase extends BaseTestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    /**
     * Создаёт application.
     *
     * @return mixed
     */
    public function createApplication()
    {
        $this->bootstrapIntegrationEnvironment();
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        DatabaseProtection::assertTestsUseIsolatedDatabase();

        return $app;
    }

    /**
     * Подготавливает окружение теста.
     */
    protected function setUp(): void
    {
        self::ensureIntegrationDatabaseExists();
        parent::setUp();
        $this->seedRoles();
    }

    private function bootstrapIntegrationEnvironment(): void
    {
        $values = ['APP_ENV' => 'testing', 'PG_INTEGRATION_TESTS' => '1', 'DB_ALLOW_DESTRUCTIVE' => '1',
            'DB_CONNECTION' => 'pgsql', 'DB_HOST' => $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? '127.0.0.1',
            'DB_PORT' => $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? '5432', 'DB_DATABASE' => 'laravel_test',
            'DB_USERNAME' => $_ENV['DB_USERNAME'] ?? $_SERVER['DB_USERNAME'] ?? 'laravel',
            'DB_PASSWORD' => $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? 'secret'];
        foreach ($values as $key => $value) {
            $stringValue = TypeCast::string($value);
            putenv("{$key}={$stringValue}");
            $_ENV[$key] = $stringValue;
            $_SERVER[$key] = $stringValue;
        }
    }

    private static function ensureIntegrationDatabaseExists(): void
    {
        $host = TypeCast::string($_ENV['DB_HOST'] ?? '127.0.0.1');
        $port = TypeCast::string($_ENV['DB_PORT'] ?? '5432');
        $database = 'laravel_test';
        $username = TypeCast::string($_ENV['DB_USERNAME'] ?? 'laravel');
        $password = TypeCast::string($_ENV['DB_PASSWORD'] ?? 'secret');
        try {
            $connection = new PDO("pgsql:host={$host};port={$port};dbname=postgres", $username, $password);
            $safeName = str_replace('"', '', $database);
            $connection->exec("CREATE DATABASE \"{$safeName}\"");
        } catch (PDOException $exception) {
            if (! str_contains($exception->getMessage(), 'already exists')) {
                throw $exception;
            }
        }
    }
}
