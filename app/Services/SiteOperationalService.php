<?php

namespace App\Services;

use App\DTO\OperationalCheckResult;
use App\DTO\OperationalProbeData;
use App\DTO\SiteOperationalStatus;
use App\Enums\OperationalService;
use App\Enums\ServiceCriticality;
use App\Models\User;
use App\Repositories\Contracts\SiteOperationalRepositoryContract;
use App\Services\Contracts\SiteOperationalServiceContract;
use App\Support\TypeCast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Сервис site operational.
 *
 * @property-read SiteOperationalRepositoryContract $operational
 */
class SiteOperationalService implements SiteOperationalServiceContract
{
    private const ASSESS_CACHE_KEY = 'site_operational:assess';

    private const ASSESS_CACHE_TTL_SECONDS = 45;

    public function __construct(protected SiteOperationalRepositoryContract $operational) {}

    /**
     * assess.

     *
     * @return SiteOperationalStatus
     */
    public function assess(): SiteOperationalStatus
    {
        /** @var array{
         *     criticalOk: bool,
         *     optionalDegraded: bool,
         *     checks: list<array{
         *         service: string,
         *         ok: bool,
         *         criticality: string,
         *         message: ?string,
         *         latencyMs: ?float
         *     }>
         * } $payload */
        $payload = Cache::remember(self::ASSESS_CACHE_KEY, self::ASSESS_CACHE_TTL_SECONDS,
            fn (): array => $this->statusToCachePayload($this->runAssess()));

        return $this->statusFromCachePayload($payload);
    }

    /**
     * Сбрасывает кэш результата проверки (для тестов и ручной
     * инвалидации).
     */
    public function forgetAssessCache(): void
    {
        Cache::forget(self::ASSESS_CACHE_KEY);
    }

    private function runAssess(): SiteOperationalStatus
    {
        $checks = collect([$this->buildResult(OperationalService::Database, $this->operational->probeDatabase()),
            $this->buildResult(OperationalService::Redis, $this->operational->probeRedis()),
            $this->buildResult(OperationalService::Storage, $this->operational->probeStorage()),
            $this->buildResult(OperationalService::Cache, $this->operational->probeCache()),
            $this->buildQueueWorkerResult()]);
        $criticalOk = $checks->filter(fn (OperationalCheckResult $check): bool => $check
            ->criticality === ServiceCriticality::Critical)->every(fn (OperationalCheckResult $check): bool => $check
            ->ok);
        $optionalDegraded = $checks->filter(fn (OperationalCheckResult $check): bool => $check
            ->criticality === ServiceCriticality::Optional)
            ->contains(fn (OperationalCheckResult $check): bool => ! $check->ok);

        return new SiteOperationalStatus(checks: $checks, criticalOk: $criticalOk, optionalDegraded: $optionalDegraded);
    }

    /**
     * Проверяет critical operational.
     */
    public function isCriticalOperational(): bool
    {
        $cached = $this->getCachedCriticalOk();

        return $cached ?? $this->assess()->criticalOk;
    }

    /**
     * Возвращает закэшированный флаг criticalOk без запуска probe.
     */
    public function getCachedCriticalOk(): ?bool
    {
        /** @var array{criticalOk: bool}|null $payload */
        $payload = Cache::get(self::ASSESS_CACHE_KEY);

        return $payload === null ? null : (bool) $payload['criticalOk'];
    }

    /**
     * Проверяет возможность bypass maintenance.
     *
     * @param  Request  $request  HTTP-запрос

     * @return bool
     */
    public function canBypassMaintenance(Request $request): bool
    {
        if (! $this->isFilamentRequest($request)) {
            return false;
        }
        $user = $request->user();

        return $user instanceof User && ($user->hasRole('owner') || $user->hasRole('admin'));
    }

    private function buildResult(OperationalService $service, OperationalProbeData $probe): OperationalCheckResult
    {
        return new OperationalCheckResult(service: $service, ok: $probe->ok,
            criticality: ServiceCriticality::forService($service), message: $probe->message,
            latencyMs: $probe->latencyMs);
    }

    private function buildQueueWorkerResult(): OperationalCheckResult
    {
        $fresh = $this->operational->isQueueWorkerHeartbeatFresh();

        return new OperationalCheckResult(service: OperationalService::QueueWorker, ok: $fresh,
            criticality: ServiceCriticality::forService(OperationalService::QueueWorker),
            message: $fresh ? null : TypeCast::string(__('site_operational.checks.queue_worker.stale')));
    }

    private function isFilamentRequest(Request $request): bool
    {
        return $request->is('admin') || $request->is('admin/*');
    }

    /**
     * @return array{criticalOk: bool, optionalDegraded: bool, checks: array<int, array{service: string, ok: bool,
     * criticality: string, message: ?string, latencyMs: ?float}>}
     */
    private function statusToCachePayload(SiteOperationalStatus $status): array
    {
        return ['criticalOk' => $status->criticalOk, 'optionalDegraded' => $status
            ->optionalDegraded, 'checks' => $status->checks
            ->map(static fn (OperationalCheckResult $check): array => ['service' => $check->service
                ->value, 'ok' => $check->ok, 'criticality' => $check->criticality->value, 'message' => $check
                ->message, 'latencyMs' => $check->latencyMs])->values()->all()];
    }

    /**
     * @param array{criticalOk: bool, optionalDegraded: bool, checks: array<int, array{service: string, ok: bool,
     * criticality: string, message: ?string, latencyMs: ?float}>} $payload
     */
    private function statusFromCachePayload(array $payload): SiteOperationalStatus
    {
        $checks = collect($payload['checks'])->map(
            static fn (array $row): OperationalCheckResult => new OperationalCheckResult(
                service: OperationalService::from($row['service']),
                ok: $row['ok'],
                criticality: ServiceCriticality::from($row['criticality']),
                message: $row['message'],
                latencyMs: $row['latencyMs'],
            ),
        );

        return new SiteOperationalStatus(checks: $checks, criticalOk: $payload['criticalOk'],
            optionalDegraded: $payload['optionalDegraded']);
    }
}
