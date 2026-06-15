<?php

namespace App\Services\Health;

use App\DTO\HealthProbeResult;
use App\DTO\OperationalCheckResult;
use App\DTO\SiteOperationalStatus;
use App\Services\Contracts\HealthCheckServiceContract;
use App\Services\Contracts\SiteOperationalServiceContract;
use App\Support\TypeCast;
use Illuminate\Support\Collection;

/**
 * Сервис health check.
 *
 * @property-read SiteOperationalServiceContract $operational
 */
final class HealthCheckService implements HealthCheckServiceContract
{
    public function __construct(protected SiteOperationalServiceContract $operational) {}

    /**
     * Выполняет одну оценку инфраструктуры (результат
     * кэшируется в SiteOperationalService).

     *
     * @return SiteOperationalStatus
     */
    public function assess(): SiteOperationalStatus
    {
        return $this->operational->assess();
    }

    /**
     * Выполняет проверки здоровья сайта.
     *
     * @return Collection<int, HealthProbeResult>
     */
    public function run(SiteOperationalStatus $status): Collection
    {
        return $status->checks->map(fn (OperationalCheckResult $check): HealthProbeResult => $this
            ->probeFromCheck($check));
    }

    /**
     * Проверяет critical ok.

     *
     * @return bool
     */
    public function isCriticalOk(SiteOperationalStatus $status): bool
    {
        return $status->criticalOk;
    }

    /**
     * Проверяет optional degraded.

     *
     * @return bool
     */
    public function isOptionalDegraded(SiteOperationalStatus $status): bool
    {
        return $status->optionalDegraded;
    }

    private function probeFromCheck(OperationalCheckResult $check): HealthProbeResult
    {
        return new HealthProbeResult(name: $check->service->value, status: $check->ok ? 'ok' : 'fail',
            message: $check->ok ? $check->message : TypeCast::string(__('health.probe_failed')),
            latencyMs: $check->latencyMs);
    }
}
