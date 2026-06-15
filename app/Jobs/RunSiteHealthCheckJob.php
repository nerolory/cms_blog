<?php

namespace App\Jobs;

use App\Services\Contracts\SiteHealthServiceContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Задача очереди run site health check job.
 *
 * @property-read ?int $triggeredBy
 */
class RunSiteHealthCheckJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public ?int $triggeredBy = null) {}

    /**
     * Обрабатывает запрос или задачу.
     */
    public function handle(SiteHealthServiceContract $siteHealthService): void
    {
        $report = $siteHealthService->runChecks($this->triggeredBy);
        $siteHealthService->persistReport($report);
    }
}
