<?php

namespace App\Support\SiteHealth\Checks;

use App\Contracts\SiteHealth\SiteHealthCheckContract;
use App\DTO\SiteHealthCheckResult;
use App\Enums\SiteHealthContext;
use App\Enums\SiteHealthSeverity;
use Illuminate\Support\Facades\DB;

/**
 * Вспомогательный класс database check.
 */
final class DatabaseCheck implements SiteHealthCheckContract
{
    /**
     * name.

     *
     * @return string
     */
    public function name(): string
    {
        return 'database';
    }

    /**
     * run.

     *
     * @return SiteHealthCheckResult
     */
    public function run(SiteHealthContext $context): SiteHealthCheckResult
    {
        try {
            DB::connection()->getPdo();
            $driver = DB::getDriverName();
            DB::select('SELECT 1');

            return new SiteHealthCheckResult(name: $this->name(), severity: SiteHealthSeverity::Passed,
                message: __('site_health.checks.database.passed'), details: $driver);
        } catch (\Throwable $exception) {
            return new SiteHealthCheckResult(name: $this->name(), severity: SiteHealthSeverity::Critical,
                message: __('site_health.checks.database.failed'), details: $exception->getMessage());
        }
    }
}
