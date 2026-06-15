<?php

namespace App\Services\Health\Probes;

use App\DTO\HealthProbeResult;
use App\Repositories\Contracts\DatabaseHealthRepositoryContract;
use App\Services\Health\Contracts\HealthProbeContract;
use Throwable;

/**
 * Сервис database health probe.
 *
 * @property-read DatabaseHealthRepositoryContract $database
 */
final class DatabaseHealthProbe implements HealthProbeContract
{
    public function __construct(protected DatabaseHealthRepositoryContract $database) {}

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
     * probe.

     *
     * @return HealthProbeResult
     */
    public function probe(): HealthProbeResult
    {
        $started = microtime(true);
        try {
            $this->database->ping();

            return new HealthProbeResult(name: $this->name(), status: 'ok',
                latencyMs: round((microtime(true) - $started) * 1000, 2));
        } catch (Throwable $exception) {
            return new HealthProbeResult(name: $this->name(), status: 'fail', message: $exception->getMessage(),
                latencyMs: round((microtime(true) - $started) * 1000, 2));
        }
    }
}
