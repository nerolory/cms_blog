<?php

namespace Tests\Feature;

use App\DTO\OperationalProbeData;
use App\DTO\SiteHealthCheckResult;
use App\Enums\SiteHealthProfile;
use App\Listeners\RecordQueueWorkerHeartbeatListener;
use App\Models\User;
use App\Repositories\Contracts\SiteOperationalRepositoryContract;
use App\Services\Contracts\SiteHealthServiceContract;
use App\Services\Contracts\SiteOperationalServiceContract;
use Illuminate\Queue\Events\Looping;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс site operational.
 */
class SiteOperationalTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    /**
     * @var list<class-string>
     */
    protected array $withoutDefaultOperationalFake = [self::class];

    /**
     * Подготавливает окружение теста.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    /**
     * test guest sees maintenance when critical service fails.
     */
    public function test_guest_sees_maintenance_when_critical_service_fails(): void
    {
        $this->mockCriticalDatabaseFailure();
        $this->get(route('home'))->assertStatus(503)->assertSee(__('maintenance.heading'));
    }

    /**
     * test moderator sees maintenance when critical service fails.
     */
    public function test_moderator_sees_maintenance_when_critical_service_fails(): void
    {
        $this->mockCriticalDatabaseFailure();
        $moderator = $this->createModeratorUser(['email' => 'mod-maint@example.com']);
        $this->actingAs($moderator)->get(route('home'))->assertStatus(503)->assertSee(__('maintenance.message'));
    }

    /**
     * test owner can access filament when critical service fails.
     */
    public function test_owner_can_access_filament_when_critical_service_fails(): void
    {
        $this->mockCriticalDatabaseFailure();
        $owner = $this->createOwnerUser(['email' => 'owner-maint@example.com']);
        $this->actingAs($owner)->get('/admin')->assertSuccessful()->assertDontSee(__('maintenance.heading'));
    }

    /**
     * test health endpoint is not blocked by maintenance middleware.
     */
    public function test_health_endpoint_is_not_blocked_by_maintenance_middleware(): void
    {
        $this->mockCriticalDatabaseFailure();
        $this->getJson(route('health'))->assertStatus(503)->assertJsonStructure(['status', 'critical_ok',
            'optional_degraded', 'checks'])->assertJsonPath('critical_ok', false);
    }

    /**
     * test health reports optional degraded when queue worker stale.
     */
    public function test_health_reports_optional_degraded_when_queue_worker_stale(): void
    {
        $this->bindOperationalRepository($this->createOperationalRepositoryMock(queueWorkerFresh: false));
        $response = $this->getJson(route('health'));
        $response->assertOk()->assertJsonPath('critical_ok', true)->assertJsonPath('optional_degraded',
            true)->assertJsonPath('status', 'degraded');
    }

    /**
     * test queue worker heartbeat listener touches repository.
     */
    public function test_queue_worker_heartbeat_listener_touches_repository(): void
    {
        $repository = $this->createMock(SiteOperationalRepositoryContract::class);
        $repository->expects($this->once())->method('touchQueueWorkerHeartbeat');
        $this->bindOperationalRepository($repository);
        app(RecordQueueWorkerHeartbeatListener::class)->handle(new Looping('sync', 'default'));
    }

    /**
     * test install check profile runs expected checks.
     */
    public function test_install_check_profile_runs_expected_checks(): void
    {
        $owner = $this->createOwnerUser(['email' => 'install-owner@example.com']);
        unset($owner);
        $report = app(SiteHealthServiceContract::class)->runChecks(null, SiteHealthProfile::InstallCheck);
        $names = $report->results->map(fn (SiteHealthCheckResult $result): string => $result->name)->all();
        $this->assertEqualsCanonicalizing(['app_key', 'migrations', 'owner_exists', 'storage_writable', 'redis',
            'queue_worker_heartbeat'], $names);
        $this->assertSame(0, $report->criticalCount());
    }

    /**
     * test install check profile fails without owner.
     */
    public function test_install_check_profile_fails_without_owner(): void
    {
        User::role('owner')->get()->each->delete();
        $report = app(SiteHealthServiceContract::class)->runChecks(null, SiteHealthProfile::InstallCheck);
        $this->assertGreaterThan(0, $report->criticalCount());
        $this->assertTrue($report->results->contains(fn ($result): bool => $result->name === 'owner_exists' && $result
            ->severity->value === 'critical'));
    }

    /**
     * test site operational service detects critical failure.
     */
    public function test_site_operational_service_detects_critical_failure(): void
    {
        $this->bindOperationalRepository($this->createOperationalRepositoryMock(databaseOk: false,
            databaseMessage: 'db down', queueWorkerFresh: false));
        $status = app(SiteOperationalServiceContract::class)->assess();
        $this->assertFalse($status->criticalOk);
    }

    /**
     * test assess caches infrastructure probes for repeated calls.
     */
    public function test_assess_caches_infrastructure_probes_for_repeated_calls(): void
    {
        $repository = $this->createMock(SiteOperationalRepositoryContract::class);
        $repository->expects($this->once())->method('probeDatabase')->willReturn(new OperationalProbeData(ok: true));
        $repository->expects($this->once())->method('probeRedis')->willReturn(new OperationalProbeData(ok: true));
        $repository->expects($this->once())->method('probeStorage')->willReturn(new OperationalProbeData(ok: true));
        $repository->expects($this->once())->method('probeCache')->willReturn(new OperationalProbeData(ok: true));
        $repository->expects($this->once())->method('isQueueWorkerHeartbeatFresh')->willReturn(true);
        $repository->method('touchQueueWorkerHeartbeat');
        $this->bindOperationalRepository($repository);

        $service = app(SiteOperationalServiceContract::class);
        $service->assess();
        $service->assess();
        $service->isCriticalOperational();
    }

    private function mockCriticalDatabaseFailure(): void
    {
        $this->bindOperationalRepository($this->createOperationalRepositoryMock(databaseOk: false,
            databaseMessage: 'db down', queueWorkerFresh: false));
    }

    private function createOperationalRepositoryMock(bool $databaseOk = true, ?string $databaseMessage = null,
        bool $queueWorkerFresh = true): SiteOperationalRepositoryContract
    {
        $repository = $this->createMock(SiteOperationalRepositoryContract::class);
        $repository->method('probeDatabase')->willReturn(new OperationalProbeData(ok: $databaseOk,
            message: $databaseMessage));
        $repository->method('probeRedis')->willReturn(new OperationalProbeData(ok: true));
        $repository->method('probeStorage')->willReturn(new OperationalProbeData(ok: true));
        $repository->method('probeCache')->willReturn(new OperationalProbeData(ok: true));
        $repository->method('isQueueWorkerHeartbeatFresh')->willReturn($queueWorkerFresh);
        $repository->method('touchQueueWorkerHeartbeat');

        return $repository;
    }

    private function bindOperationalRepository(SiteOperationalRepositoryContract $repository): void
    {
        $this->app->instance(SiteOperationalRepositoryContract::class, $repository);
    }
}
