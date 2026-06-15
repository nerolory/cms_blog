<?php

namespace App\Support\SiteHealth\Checks;

use App\Contracts\SiteHealth\SiteHealthCheckContract;
use App\DTO\SiteHealthCheckResult;
use App\Enums\SiteHealthContext;
use App\Enums\SiteHealthSeverity;
use App\Support\TypeCast;
use Illuminate\Foundation\Application;

/**
 * Вспомогательный класс laravel version check.
 *
 * @property-read Application $app
 */
final class LaravelVersionCheck implements SiteHealthCheckContract
{
    public function __construct(private Application $app) {}

    /**
     * name.

     *
     * @return string
     */
    public function name(): string
    {
        return 'laravel';
    }

    /**
     * run.

     *
     * @return SiteHealthCheckResult
     */
    public function run(SiteHealthContext $context): SiteHealthCheckResult
    {
        $version = $this->app->version();
        $required = TypeCast::string(config('site_health.laravel.min_version', '11.0.0'));
        $ok = version_compare($version, $required, '>=');

        return new SiteHealthCheckResult(name: $this->name(),
            severity: $ok ? SiteHealthSeverity::Passed : SiteHealthSeverity::Critical,
            message: $ok ? __('site_health.checks.laravel.passed',
                ['version' => $version]) : __('site_health.checks.laravel.failed', ['current' => $version,
                    'required' => $required]), details: 'Laravel '.$version);
    }
}
