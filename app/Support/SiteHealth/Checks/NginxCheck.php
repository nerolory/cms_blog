<?php

namespace App\Support\SiteHealth\Checks;

use App\Contracts\SiteHealth\SiteHealthCheckContract;
use App\DTO\SiteHealthCheckResult;
use App\Enums\SiteHealthContext;
use App\Enums\SiteHealthSeverity;
use App\Support\TypeCast;
use Illuminate\Support\Facades\Http;

/**
 * Вспомогательный класс nginx check.
 */
final class NginxCheck implements SiteHealthCheckContract
{
    /**
     * name.

     *
     * @return string
     */
    public function name(): string
    {
        return 'nginx';
    }

    /**
     * run.

     *
     * @return SiteHealthCheckResult
     */
    public function run(SiteHealthContext $context): SiteHealthCheckResult
    {
        return match ($context) {
            SiteHealthContext::Docker => $this->checkDocker(),
            SiteHealthContext::K8s => $this->checkHttp(),
            SiteHealthContext::Native => $this->checkNative(),
        };
    }

    private function checkDocker(): SiteHealthCheckResult
    {
        $host = TypeCast::string(config('site_health.nginx.docker_host', 'nginx'));
        $port = TypeCast::int(config('site_health.nginx.docker_port', 80), 80);
        try {
            $response = Http::timeout(5)->get("http://{$host}:{$port}/up");
            if ($response->successful()) {
                return new SiteHealthCheckResult(name: $this->name(), severity: SiteHealthSeverity::Passed,
                    message: __('site_health.checks.nginx.passed'), details: "{$host}:{$port}");
            }
        } catch (\Throwable $exception) {
            return new SiteHealthCheckResult(name: $this->name(), severity: SiteHealthSeverity::Warning,
                message: __('site_health.checks.nginx.unreachable'), details: $exception->getMessage());
        }

        return new SiteHealthCheckResult(name: $this->name(), severity: SiteHealthSeverity::Warning,
            message: __('site_health.checks.nginx.unhealthy'));
    }

    private function checkHttp(): SiteHealthCheckResult
    {
        return $this->checkHttpEndpoint(TypeCast::string(config('app.url')).'/up');
    }

    private function checkNative(): SiteHealthCheckResult
    {
        $configPath = config('site_health.nginx.config_path');
        if (is_string($configPath) && $configPath !== '' && is_readable($configPath)) {
            return new SiteHealthCheckResult(name: $this->name(), severity: SiteHealthSeverity::Passed,
                message: __('site_health.checks.nginx.config_found'), details: $configPath);
        }

        return $this->checkHttpEndpoint(url('/up'));
    }

    private function checkHttpEndpoint(string $url): SiteHealthCheckResult
    {
        try {
            $response = Http::timeout(5)->get($url);
            if ($response->successful()) {
                return new SiteHealthCheckResult(name: $this->name(), severity: SiteHealthSeverity::Passed,
                    message: __('site_health.checks.nginx.passed'), details: $url);
            }
        } catch (\Throwable $exception) {
            return new SiteHealthCheckResult(name: $this->name(), severity: SiteHealthSeverity::Warning,
                message: __('site_health.checks.nginx.unreachable'), details: $exception->getMessage());
        }

        return new SiteHealthCheckResult(name: $this->name(), severity: SiteHealthSeverity::Warning,
            message: __('site_health.checks.nginx.unhealthy'));
    }
}
