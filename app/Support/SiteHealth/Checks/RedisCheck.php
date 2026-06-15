<?php

namespace App\Support\SiteHealth\Checks;

use App\Contracts\SiteHealth\SiteHealthCheckContract;
use App\DTO\SiteHealthCheckResult;
use App\Enums\SiteHealthContext;
use App\Enums\SiteHealthSeverity;
use Illuminate\Support\Facades\Redis;

/**
 * Вспомогательный класс redis check.
 */
final class RedisCheck implements SiteHealthCheckContract
{
    /**
     * name.

     *
     * @return string
     */
    public function name(): string
    {
        return 'redis';
    }

    /**
     * run.

     *
     * @return SiteHealthCheckResult
     */
    public function run(SiteHealthContext $context): SiteHealthCheckResult
    {
        if (config('cache.default') !== 'redis' && config('queue.default') !== 'redis') {
            return new SiteHealthCheckResult(name: $this->name(), severity: SiteHealthSeverity::Warning,
                message: __('site_health.checks.redis.not_configured'));
        }
        try {
            $pong = Redis::connection()->ping();

            return new SiteHealthCheckResult(name: $this->name(), severity: SiteHealthSeverity::Passed,
                message: __('site_health.checks.redis.passed'), details: is_string($pong) ? $pong : 'PONG');
        } catch (\Throwable $exception) {
            return new SiteHealthCheckResult(name: $this->name(), severity: SiteHealthSeverity::Critical,
                message: __('site_health.checks.redis.failed'), details: $exception->getMessage());
        }
    }
}
