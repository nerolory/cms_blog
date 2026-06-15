<?php

namespace App\Support\SiteHealth\Checks;

use App\Contracts\SiteHealth\SiteHealthCheckContract;
use App\DTO\SiteHealthCheckResult;
use App\Enums\SiteHealthContext;
use App\Enums\SiteHealthSeverity;
use App\Repositories\Contracts\SiteOperationalRepositoryContract;

/**
 * Вспомогательный класс app key check.
 *
 * @property-read SiteOperationalRepositoryContract $operational
 */
final class AppKeyCheck implements SiteHealthCheckContract
{
    public function __construct(protected SiteOperationalRepositoryContract $operational) {}

    /**
     * name.

     *
     * @return string
     */
    public function name(): string
    {
        return 'app_key';
    }

    /**
     * run.

     *
     * @return SiteHealthCheckResult
     */
    public function run(SiteHealthContext $context): SiteHealthCheckResult
    {
        $ok = $this->operational->isAppKeyConfigured();

        return new SiteHealthCheckResult(name: $this->name(),
            severity: $ok ? SiteHealthSeverity::Passed : SiteHealthSeverity::Critical,
            message: $ok ? __('site_health.checks.app_key.passed') : __('site_health.checks.app_key.failed'));
    }
}
