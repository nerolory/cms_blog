<?php

namespace App\Support\SiteHealth\Checks;

use App\Contracts\SiteHealth\SiteHealthCheckContract;
use App\DTO\SiteHealthCheckResult;
use App\Enums\SiteHealthContext;
use App\Enums\SiteHealthSeverity;
use App\Repositories\Contracts\SiteOperationalRepositoryContract;

/**
 * Вспомогательный класс migrations up to date check.
 *
 * @property-read SiteOperationalRepositoryContract $operational
 */
final class MigrationsUpToDateCheck implements SiteHealthCheckContract
{
    public function __construct(protected SiteOperationalRepositoryContract $operational) {}

    /**
     * name.

     *
     * @return string
     */
    public function name(): string
    {
        return 'migrations';
    }

    /**
     * run.

     *
     * @return SiteHealthCheckResult
     */
    public function run(SiteHealthContext $context): SiteHealthCheckResult
    {
        $pending = $this->operational->hasPendingMigrations();

        return new SiteHealthCheckResult(
            name: $this->name(),
            severity: $pending ? SiteHealthSeverity::Critical : SiteHealthSeverity::Passed,
            message: $pending
                ? __('site_health.checks.migrations.pending')
                : __('site_health.checks.migrations.passed'),
        );
    }
}
