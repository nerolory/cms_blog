<?php

namespace App\Repositories\Contracts;

use App\DTO\OperationalProbeData;

/**
 * Контракт репозитория site operational.
 */
interface SiteOperationalRepositoryContract
{
    /**
     * probe database.

     *
     * @return OperationalProbeData
     */
    public function probeDatabase(): OperationalProbeData;

    /**
     * probe redis.

     *
     * @return OperationalProbeData
     */
    public function probeRedis(): OperationalProbeData;

    /**
     * probe storage.

     *
     * @return OperationalProbeData
     */
    public function probeStorage(): OperationalProbeData;

    /**
     * probe cache.

     *
     * @return OperationalProbeData
     */
    public function probeCache(): OperationalProbeData;

    /**
     * Проверяет queue worker heartbeat fresh.

     *
     * @return bool
     */
    public function isQueueWorkerHeartbeatFresh(): bool;

    /**
     * touch queue worker heartbeat.
     */
    public function touchQueueWorkerHeartbeat(): void;

    /**
     * Проверяет наличие pending migrations.

     *
     * @return bool
     */
    public function hasPendingMigrations(): bool;

    /**
     * Проверяет app key configured.

     *
     * @return bool
     */
    public function isAppKeyConfigured(): bool;
}
