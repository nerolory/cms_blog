<?php

namespace Tests\Unit;

use App\Support\Config\ProductionConfigGuard;
use RuntimeException;
use Tests\TestCase;

/**
 * Production guards: SEED_OWNER_PASSWORD и HEALTH_ALLOWED_IPS.
 */
class ProductionConfigGuardTest extends TestCase
{
    /**
     * test owner password guard allows strong password in production.
     */
    public function test_owner_password_guard_allows_strong_password_in_production(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        config(['seeding.owner.password' => 'strong-production-secret']);
        ProductionConfigGuard::assertOwnerPasswordSafe();
        $this->addToAssertionCount(1);
    }

    /**
     * test owner password guard rejects default in production.
     */
    public function test_owner_password_guard_rejects_default_in_production(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        config(['seeding.owner.password' => 'password']);
        $this->expectException(RuntimeException::class);
        ProductionConfigGuard::assertOwnerPasswordSafe();
    }

    /**
     * test owner password guard skips outside production.
     */
    public function test_owner_password_guard_skips_outside_production(): void
    {
        config(['seeding.owner.password' => 'password']);
        ProductionConfigGuard::assertOwnerPasswordSafe();
        $this->addToAssertionCount(1);
    }

    /**
     * test health allowlist guard requires ips in production.
     */
    public function test_health_allowlist_guard_requires_ips_in_production(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        config(['health.allowed_ips' => [], 'app.key' => 'base64:'.base64_encode('test-key-32-bytes-long!!')]);
        $this->expectException(RuntimeException::class);
        ProductionConfigGuard::assertBootRequirements();
    }

    /**
     * test health allowlist guard passes when configured in production.
     */
    public function test_health_allowlist_guard_passes_when_configured_in_production(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        config([
            'health.allowed_ips' => ['127.0.0.1'],
            'app.key' => 'base64:'.base64_encode('test-key-32-bytes-long!!'),
        ]);
        ProductionConfigGuard::assertBootRequirements();
        $this->addToAssertionCount(1);
    }

    /**
     * test health allowlist guard skips before app key is generated.
     */
    public function test_health_allowlist_guard_skips_incomplete_bootstrap_in_production(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        config(['health.allowed_ips' => [], 'app.key' => '']);
        ProductionConfigGuard::assertBootRequirements();
        $this->addToAssertionCount(1);
    }
}
