<?php

namespace App\Services\Health\Probes;

use App\DTO\HealthProbeResult;
use App\Services\Health\Contracts\HealthProbeContract;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Сервис storage health probe.
 */
final class StorageHealthProbe implements HealthProbeContract
{
    /**
     * name.

     *
     * @return string
     */
    public function name(): string
    {
        return 'storage';
    }

    /**
     * probe.

     *
     * @return HealthProbeResult
     */
    public function probe(): HealthProbeResult
    {
        $started = microtime(true);
        $path = 'health/'.Str::uuid()->toString().'.txt';
        try {
            Storage::disk('local')->put($path, 'ok');
            $contents = Storage::disk('local')->get($path);
            Storage::disk('local')->delete($path);

            return new HealthProbeResult(name: $this->name(), status: $contents === 'ok' ? 'ok' : 'fail',
                message: $contents === 'ok' ? null : 'Storage read/write mismatch.',
                latencyMs: round((microtime(true) - $started) * 1000, 2));
        } catch (Throwable $exception) {
            return new HealthProbeResult(name: $this->name(), status: 'fail', message: $exception->getMessage(),
                latencyMs: round((microtime(true) - $started) * 1000, 2));
        }
    }
}
