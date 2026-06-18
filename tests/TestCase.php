<?php

namespace Tests;

use App\Support\Database\DatabaseProtection;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\FakesSiteOperational;

/**
 * Класс test case.
 */
abstract class TestCase extends BaseTestCase
{
    use FakesSiteOperational;

    /**
     * @var list<class-string>
     */
    protected array $withoutDefaultOperationalFake = [];

    /**
     * Создаёт application.
     *
     * @return mixed
     */
    public function createApplication()
    {
        $this->enforceTestingDatabaseEnv();

        return parent::createApplication();
    }

    /**
     * enforce testing database env.
     */
    protected function enforceTestingDatabaseEnv(): void
    {
        foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:',
            'DB_URL' => ''] as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    /**
     * Подготавливает окружение теста.
     */
    protected function setUp(): void
    {
        parent::setUp();
        DatabaseProtection::assertTestsUseIsolatedDatabase();
        Cache::flush();
        try {
            Redis::connection()->flushdb();
        } catch (\Throwable) {
        }
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        if (! $this->usesDefaultOperationalFake()) {
            return;
        }
        $this->fakeSiteOperational();
    }

    /**
     * uses default operational fake.

     *
     * @return bool
     */
    protected function usesDefaultOperationalFake(): bool
    {
        return ! in_array(static::class, $this->withoutDefaultOperationalFake, true);
    }
}
