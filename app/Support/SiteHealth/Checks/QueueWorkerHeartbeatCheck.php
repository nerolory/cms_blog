<?php

namespace App\Support\SiteHealth\Checks;

use App\Contracts\SiteHealth\SiteHealthCheckContract;
use App\DTO\SiteHealthCheckResult;
use App\Enums\SiteHealthContext;
use App\Enums\SiteHealthSeverity;
use App\Repositories\Contracts\SiteOperationalRepositoryContract;

/**
 * Вспомогательный класс queue worker heartbeat check.
 *
 * @property-read SiteOperationalRepositoryContract $operational
 */
final class QueueWorkerHeartbeatCheck implements SiteHealthCheckContract
{
    public function __construct(protected SiteOperationalRepositoryContract $operational) {}

    /**
     * name.

     *
     * @return string
     */
    public function name(): string
    {
        return 'queue_worker_heartbeat';
    }

    /**
     * run.

     *
     * @return SiteHealthCheckResult
     */
    public function run(SiteHealthContext $context): SiteHealthCheckResult
    {
        $fresh = $this->operational->isQueueWorkerHeartbeatFresh();

        return new SiteHealthCheckResult(
            name: $this->name(),
            severity: $fresh ? SiteHealthSeverity::Passed : SiteHealthSeverity::Warning,
            message: $fresh
                ? __('site_health.checks.queue_worker_heartbeat.passed')
                : __('site_health.checks.queue_worker_heartbeat.stale'),
        );
    }
}
