<?php

namespace App\Support\SiteHealth\Checks;

use App\Contracts\SiteHealth\SiteHealthCheckContract;
use App\DTO\SiteHealthCheckResult;
use App\Enums\SiteHealthContext;
use App\Enums\SiteHealthSeverity;
use App\Repositories\Contracts\UserRepositoryContract;

/**
 * Вспомогательный класс owner exists check.
 *
 * @property-read UserRepositoryContract $users
 */
final class OwnerExistsCheck implements SiteHealthCheckContract
{
    public function __construct(protected UserRepositoryContract $users) {}

    /**
     * name.

     *
     * @return string
     */
    public function name(): string
    {
        return 'owner_exists';
    }

    /**
     * run.

     *
     * @return SiteHealthCheckResult
     */
    public function run(SiteHealthContext $context): SiteHealthCheckResult
    {
        $ok = $this->users->existsWithRole('owner');

        return new SiteHealthCheckResult(name: $this->name(),
            severity: $ok ? SiteHealthSeverity::Passed : SiteHealthSeverity::Critical,
            message: $ok ? __('site_health.checks.owner_exists.passed') : __('site_health.checks.owner_exists.failed'));
    }
}
