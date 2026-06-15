<?php

namespace App\DTO;

use Illuminate\Support\Collection;

/**
 * DTO site operational status.
 *
 * @property-read Collection<int, OperationalCheckResult> $checks
 * @property-read bool $criticalOk
 * @property-read bool $optionalDegraded
 */
readonly class SiteOperationalStatus
{
    /**
     * @param  Collection<int, OperationalCheckResult>  $checks
     */
    public function __construct(public Collection $checks, public bool $criticalOk, public bool $optionalDegraded) {}

    /**
     * Проверяет fully healthy.

     *
     * @return bool
     */
    public function isFullyHealthy(): bool
    {
        return $this->criticalOk && ! $this->optionalDegraded;
    }
}
