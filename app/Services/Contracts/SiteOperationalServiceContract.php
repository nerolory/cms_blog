<?php

namespace App\Services\Contracts;

use App\DTO\SiteOperationalStatus;
use Illuminate\Http\Request;

/**
 * Контракт сервиса site operational.
 */
interface SiteOperationalServiceContract
{
    /**
     * assess.

     *
     * @return SiteOperationalStatus
     */
    public function assess(): SiteOperationalStatus;

    /**
     * Проверяет critical operational.

     *
     * @return bool
     */
    public function isCriticalOperational(): bool;

    /**
     * Проверяет возможность bypass maintenance.
     *
     * @param  Request  $request  HTTP-запрос

     * @return bool
     */
    public function canBypassMaintenance(Request $request): bool;
}
