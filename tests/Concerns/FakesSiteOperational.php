<?php

namespace Tests\Concerns;

use App\DTO\SiteOperationalStatus;
use App\Services\Contracts\SiteOperationalServiceContract;

/**
 * Подмена операционного статуса сайта в feature-тестах.
 */
trait FakesSiteOperational
{
    /**
     * fake site operational.
     *
     * @param  bool  $criticalOk  ok
     * @param  bool  $optionalDegraded  degraded
     * @param  bool  $bypassMaintenance  maintenance
     */
    protected function fakeSiteOperational(bool $criticalOk = true, bool $optionalDegraded = false,
        bool $bypassMaintenance = false): void
    {
        $status = new SiteOperationalStatus(collect(), $criticalOk, $optionalDegraded);
        $mock = $this->createMock(SiteOperationalServiceContract::class);
        $mock->method('assess')->willReturn($status);
        $mock->method('isCriticalOperational')->willReturn($criticalOk);
        $mock->method('canBypassMaintenance')->willReturn($bypassMaintenance);
        $this->app->instance(SiteOperationalServiceContract::class, $mock);
    }
}
