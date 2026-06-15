<?php

namespace App\Services\Health\Contracts;

use App\DTO\HealthProbeResult;

/**
 * Сервис health probe.
 */
interface HealthProbeContract
{
    /**
     * name.

     *
     * @return string
     */
    public function name(): string;

    /**
     * probe.

     *
     * @return HealthProbeResult
     */
    public function probe(): HealthProbeResult;
}
