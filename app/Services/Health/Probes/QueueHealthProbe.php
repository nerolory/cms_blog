<?php

namespace App\Services\Health\Probes;

use App\DTO\HealthProbeResult;
use App\Services\Health\Contracts\HealthProbeContract;
use Illuminate\Support\Facades\Queue;
use Throwable;

/**
 * Сервис queue health probe.
 */
final class QueueHealthProbe implements HealthProbeContract
{
    /**
     * name.

     *
     * @return string
     */
    public function name(): string
    {
        return 'queue';
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
            $connection = Queue::connection();
            $size = $connection->size();

            return new HealthProbeResult(name: $this->name(), status: 'ok', message: 'Pending jobs: '.$size,
                latencyMs: round((microtime(true) - $started) * 1000, 2));
        } catch (Throwable $exception) {
            return new HealthProbeResult(name: $this->name(), status: 'fail', message: $exception->getMessage(),
                latencyMs: round((microtime(true) - $started) * 1000, 2));
        }
    }
}
