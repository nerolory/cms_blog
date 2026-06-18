<?php

namespace App\Repositories;

use App\DTO\OperationalProbeData;
use App\Repositories\Contracts\SiteOperationalRepositoryContract;
use App\Support\TypeCast;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Репозиторий site operational.
 *
 * @property-read DatabaseManager $database
 * @property-read Migrator $migrator
 */
class SiteOperationalRepository implements SiteOperationalRepositoryContract
{
    public function __construct(protected DatabaseManager $database, protected Migrator $migrator) {}

    /**
     * probe database.

     *
     * @return OperationalProbeData
     */
    public function probeDatabase(): OperationalProbeData
    {
        $started = microtime(true);
        try {
            $this->database->connection()->select('select 1 as ok');

            return new OperationalProbeData(ok: true, latencyMs: $this->elapsedMs($started));
        } catch (Throwable $exception) {
            return new OperationalProbeData(ok: false, message: $exception->getMessage(),
                latencyMs: $this->elapsedMs($started));
        }
    }

    /**
     * probe redis.

     *
     * @return OperationalProbeData
     */
    public function probeRedis(): OperationalProbeData
    {
        if (app()->environment('testing')) {
            return new OperationalProbeData(ok: true);
        }
        $started = microtime(true);
        try {
            $pong = Redis::connection()->ping();

            return new OperationalProbeData(ok: (bool) $pong, message: $pong ? null : 'Redis ping returned false.',
                latencyMs: $this->elapsedMs($started));
        } catch (Throwable $exception) {
            return new OperationalProbeData(ok: false, message: $exception->getMessage(),
                latencyMs: $this->elapsedMs($started));
        }
    }

    /**
     * probe storage.

     *
     * @return OperationalProbeData
     */
    public function probeStorage(): OperationalProbeData
    {
        $started = microtime(true);
        $path = 'health/'.Str::uuid()->toString().'.txt';
        try {
            $written = Storage::disk('local')->put($path, 'ok');
            if ($written !== true) {
                return new OperationalProbeData(ok: false, message: 'Storage write failed.',
                    latencyMs: $this->elapsedMs($started));
            }
            $contents = Storage::disk('local')->get($path);
            Storage::disk('local')->delete($path);

            return new OperationalProbeData(ok: $contents === 'ok',
                message: $contents === 'ok' ? null : 'Storage read/write mismatch.',
                latencyMs: $this->elapsedMs($started));
        } catch (Throwable $exception) {
            return new OperationalProbeData(ok: false, message: $exception->getMessage(),
                latencyMs: $this->elapsedMs($started));
        }
    }

    /**
     * probe cache.

     *
     * @return OperationalProbeData
     */
    public function probeCache(): OperationalProbeData
    {
        $started = microtime(true);
        $key = 'health:probe:'.Str::uuid()->toString();
        try {
            Cache::put($key, 'ok', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            return new OperationalProbeData(ok: $value === 'ok',
                message: $value === 'ok' ? null : 'Cache read/write mismatch.', latencyMs: $this->elapsedMs($started));
        } catch (Throwable $exception) {
            return new OperationalProbeData(ok: false, message: $exception->getMessage(),
                latencyMs: $this->elapsedMs($started));
        }
    }

    /**
     * Проверяет queue worker heartbeat fresh.

     *
     * @return bool
     */
    public function isQueueWorkerHeartbeatFresh(): bool
    {
        try {
            $value = Redis::connection()->get($this->heartbeatKey());

            return $value !== null && $value !== false && $value !== '';
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * touch queue worker heartbeat.
     */
    public function touchQueueWorkerHeartbeat(): void
    {
        try {
            Redis::connection()->setex($this->heartbeatKey(),
                TypeCast::int(config('site_operational.queue_heartbeat.ttl_seconds', 120), 120),
                (string) now()->timestamp);
        } catch (Throwable) {
            // Heartbeat is best-effort; operational checks surface Redis issues separately.
        }
    }

    /**
     * Проверяет наличие pending migrations.

     *
     * @return bool
     */
    public function hasPendingMigrations(): bool
    {
        $files = $this->migrator->getMigrationFiles($this->migrator->paths());
        $repository = $this->migrator->getRepository();
        if (! $repository->repositoryExists()) {
            return $files !== [];
        }
        $pending = array_diff(array_keys($files), $repository->getRan());

        return $pending !== [];
    }

    /**
     * Проверяет app key configured.

     *
     * @return bool
     */
    public function isAppKeyConfigured(): bool
    {
        $key = TypeCast::string(config('app.key', ''));
        if ($key === '') {
            return false;
        }

        return ! in_array($key, ['base64:', 'SomeRandomString', 'base64:SomeRandomString'], true);
    }

    private function heartbeatKey(): string
    {
        return TypeCast::string(config('site_operational.queue_heartbeat.redis_key', 'queue:worker:heartbeat'));
    }

    private function elapsedMs(float $started): float
    {
        return round((microtime(true) - $started) * 1000, 2);
    }
}
