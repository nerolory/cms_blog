<?php

namespace App\DTO;

/**
 * Метрики SLA модерации для админ-панели.
 *
 * @property-read int $pendingCount
 * @property-read ?int $oldestAgeMinutes
 * @property-read int $slaMinutes
 */
readonly class ModerationSlaStats
{
    public function __construct(public int $pendingCount, public ?int $oldestAgeMinutes, public int $slaMinutes) {}

    /**
     * Проверяет breached.

     *
     * @return bool
     */
    public function isBreached(): bool
    {
        return $this->oldestAgeMinutes !== null && $this->oldestAgeMinutes > $this->slaMinutes;
    }
}
