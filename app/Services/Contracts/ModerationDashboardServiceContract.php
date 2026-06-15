<?php

namespace App\Services\Contracts;

use App\DTO\ModerationSlaStats;

/**
 * Контракт метрик модерации для Filament.
 */
interface ModerationDashboardServiceContract
{
    /**
     * sla stats.

     *
     * @return ModerationSlaStats
     */
    public function slaStats(): ModerationSlaStats;
}
