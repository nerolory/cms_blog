<?php

namespace App\Support\SiteHealth\Checks;

use App\Contracts\SiteHealth\SiteHealthCheckContract;
use App\DTO\SiteHealthCheckResult;
use App\Enums\SiteHealthContext;
use App\Enums\SiteHealthSeverity;
use Illuminate\Support\Facades\Http;

/**
 * Вспомогательный класс security headers check.
 */
final class SecurityHeadersCheck implements SiteHealthCheckContract
{
    /**
     * @var list<string>
     */
    private const REQUIRED_HEADERS = ['X-Frame-Options', 'X-Content-Type-Options', 'Referrer-Policy'];

    /**
     * name.

     *
     * @return string
     */
    public function name(): string
    {
        return 'security_headers';
    }

    /**
     * run.

     *
     * @return SiteHealthCheckResult
     */
    public function run(SiteHealthContext $context): SiteHealthCheckResult
    {
        try {
            $response = Http::timeout(5)->get(url('/'));
            $missing = [];
            foreach (self::REQUIRED_HEADERS as $header) {
                if ($response->header($header) === null) {
                    $missing[] = $header;
                }
            }
            if ($missing === []) {
                return new SiteHealthCheckResult(name: $this->name(), severity: SiteHealthSeverity::Passed,
                    message: __('site_health.checks.security_headers.passed'));
            }

            return new SiteHealthCheckResult(name: $this->name(), severity: SiteHealthSeverity::Warning,
                message: __('site_health.checks.security_headers.missing'), details: implode(', ', $missing));
        } catch (\Throwable $exception) {
            return new SiteHealthCheckResult(name: $this->name(), severity: SiteHealthSeverity::Warning,
                message: __('site_health.checks.security_headers.failed'), details: $exception->getMessage());
        }
    }
}
