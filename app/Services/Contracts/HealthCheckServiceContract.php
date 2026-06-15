<?php

namespace App\Services\Contracts;

use App\DTO\HealthProbeResult;
use App\DTO\SiteOperationalStatus;
use Illuminate\Support\Collection;

/**
 * Контракт сервиса публичного health-check.
 */
interface HealthCheckServiceContract
{
    /**
     * Выполняет одну оценку инфраструктуры.
     *
     * @return SiteOperationalStatus
     */
    public function assess(): SiteOperationalStatus;

    /**
     * Преобразует результат оценки в набор проб для JSON-ответа.
     *
     * @return Collection<int, HealthProbeResult>
     */
    public function run(SiteOperationalStatus $status): Collection;

    /**
     * Проверяет, что все критичные сервисы доступны.
     *
     * @return bool
     */
    public function isCriticalOk(SiteOperationalStatus $status): bool;

    /**
     * Проверяет деградацию необязательных сервисов.
     *
     * @return bool
     */
    public function isOptionalDegraded(SiteOperationalStatus $status): bool;
}
