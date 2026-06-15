<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Класс health endpoint.
 */
class HealthEndpointTest extends TestCase
{
    /**
     * @var list<class-string>
     */
    protected array $withoutDefaultOperationalFake = [self::class];

    /**
     * test health returns probe payload.
     */
    public function test_health_returns_probe_payload(): void
    {
        $response = $this->getJson(route('health'));
        $response->assertJsonStructure(['status', 'critical_ok', 'optional_degraded', 'checks' => ['*' => ['name',
            'status', 'message', 'latency_ms']]]);
    }

    /**
     * test health includes database probe.
     */
    public function test_health_includes_database_probe(): void
    {
        $response = $this->getJson(route('health'));
        /** @var list<array{name: string, status: string}> $checks */
        $checks = $response->json('checks');
        $this->assertTrue(collect($checks)->contains(fn (array $check): bool => ($check['name'] ?? '') === 'database'));
    }

    /**
     * test health returns 200 when critical ok.
     */
    public function test_health_returns_200_when_critical_ok(): void
    {
        $response = $this->getJson(route('health'));
        $response->assertOk()->assertJsonPath('critical_ok', true);
    }

    /**
     * test health returns minimal payload in production for public callers.
     */
    public function test_health_returns_minimal_payload_in_production(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        config(['health.allowed_ips' => [], 'health.expose_probe_details' => null]);
        $response = $this->getJson(route('health'));
        $response->assertJsonStructure(['status']);
        $response->assertJsonMissingPath('checks');
        $response->assertJsonMissingPath('critical_ok');
    }

    /**
     * test health rejects disallowed ip when allowlist configured.
     */
    public function test_health_rejects_disallowed_ip_when_allowlist_configured(): void
    {
        config(['health.allowed_ips' => ['203.0.113.1']]);
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.50'])->getJson(route('health'))->assertForbidden();
    }

    /**
     * test health exposes probes for allowlisted ip in production.
     */
    public function test_health_exposes_probes_for_allowlisted_ip_in_production(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        config(['health.allowed_ips' => ['127.0.0.1'], 'health.expose_probe_details' => null]);
        $response = $this->getJson(route('health'));
        $response->assertJsonStructure(['status', 'critical_ok', 'checks']);
    }
}
