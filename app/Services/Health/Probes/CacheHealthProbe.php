<?php

namespace App\Services\Health\Probes;

use App\DTO\HealthProbeResult;
use App\Services\Health\Contracts\HealthProbeContract;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

/**
 * Сервис cache health probe.
 */
final class CacheHealthProbe implements HealthProbeContract
{
    /**
     * name.

     *
     * @return string
     */
    public function name(): string
    {
        return 'cache';
    }

    /**
     * probe.

     *
     * @return HealthProbeResult
     */
    public function probe(): HealthProbeResult
    {
        $started = microtime(true);
        $key = 'health:probe:'.Str::uuid()->toString();
        try {
            Cache::put($key, 'ok', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            return new HealthProbeResult(name: $this->name(), status: $value === 'ok' ? 'ok' : 'fail',
                message: $value === 'ok' ? null : 'Cache read/write mismatch.',
                latencyMs: round((microtime(true) - $started) * 1000, 2));
        } catch (Throwable $exception) {
            return new HealthProbeResult(name: $this->name(), status: 'fail', message: $exception->getMessage(),
                latencyMs: round((microtime(true) - $started) * 1000, 2));
        }
    }
}
