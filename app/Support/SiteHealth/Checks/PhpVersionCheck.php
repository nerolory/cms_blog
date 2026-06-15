<?php

namespace App\Support\SiteHealth\Checks;

use App\Contracts\SiteHealth\SiteHealthCheckContract;
use App\DTO\SiteHealthCheckResult;
use App\Enums\SiteHealthContext;
use App\Enums\SiteHealthSeverity;
use App\Support\TypeCast;

/**
 * Вспомогательный класс php version check.
 */
final class PhpVersionCheck implements SiteHealthCheckContract
{
    /**
     * name.

     *
     * @return string
     */
    public function name(): string
    {
        return 'php';
    }

    /**
     * run.

     *
     * @return SiteHealthCheckResult
     */
    public function run(SiteHealthContext $context): SiteHealthCheckResult
    {
        $required = TypeCast::string(config('site_health.php.min_version', '8.2.0'));
        $current = PHP_VERSION;
        $ok = version_compare($current, $required, '>=');

        return new SiteHealthCheckResult(name: $this->name(),
            severity: $ok ? SiteHealthSeverity::Passed : SiteHealthSeverity::Critical,
            message: $ok ? __('site_health.checks.php.passed',
                ['version' => $current]) : __('site_health.checks.php.failed', ['current' => $current,
                    'required' => $required]), details: 'PHP '.PHP_VERSION.' ('.PHP_SAPI.')');
    }
}
