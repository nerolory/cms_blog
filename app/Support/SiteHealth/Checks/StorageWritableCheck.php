<?php

namespace App\Support\SiteHealth\Checks;

use App\Contracts\SiteHealth\SiteHealthCheckContract;
use App\DTO\SiteHealthCheckResult;
use App\Enums\SiteHealthContext;
use App\Enums\SiteHealthSeverity;
use App\Repositories\Contracts\SiteOperationalRepositoryContract;

/**
 * Вспомогательный класс storage writable check.
 *
 * @property-read SiteOperationalRepositoryContract $operational
 */
final class StorageWritableCheck implements SiteHealthCheckContract
{
    public function __construct(protected SiteOperationalRepositoryContract $operational) {}

    /**
     * name.

     *
     * @return string
     */
    public function name(): string
    {
        return 'storage_writable';
    }

    /**
     * run.

     *
     * @return SiteHealthCheckResult
     */
    public function run(SiteHealthContext $context): SiteHealthCheckResult
    {
        $probe = $this->operational->probeStorage();

        return new SiteHealthCheckResult(
            name: $this->name(),
            severity: $probe->ok ? SiteHealthSeverity::Passed : SiteHealthSeverity::Critical,
            message: $probe->ok
                ? __('site_health.checks.storage_writable.passed')
                : __('site_health.checks.storage_writable.failed'),
            details: $probe->message,
        );
    }
}
