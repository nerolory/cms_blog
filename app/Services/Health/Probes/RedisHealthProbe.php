<?php

namespace App\Services\Health\Probes;

use App\DTO\HealthProbeResult;
use App\Services\Health\Contracts\HealthProbeContract;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Сервис redis health probe.
 */
final class RedisHealthProbe implements HealthProbeContract
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
     * probe.

     *
     * @return HealthProbeResult
     */
    public function probe(): HealthProbeResult
    {
        $started = microtime(true);
        try {
            $pong = Redis::connection()->ping();

            return new HealthProbeResult(name: $this->name(), status: $pong ? 'ok' : 'fail',
                message: $pong ? null : 'Redis ping returned false.',
                latencyMs: round((microtime(true) - $started) * 1000, 2));
        } catch (Throwable $exception) {
            return new HealthProbeResult(name: $this->name(), status: 'fail', message: $exception->getMessage(),
                latencyMs: round((microtime(true) - $started) * 1000, 2));
        }
    }
}
