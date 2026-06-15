<?php

namespace App\Services;

use App\DTO\ModerationSlaStats;
use App\Repositories\Contracts\PostRepositoryContract;
use App\Services\Contracts\ModerationDashboardServiceContract;
use App\Support\TypeCast;

/**
 * Агрегированные метрики модерации для админ-виджетов.
 *
 * @property-read PostRepositoryContract $posts
 */
class ModerationDashboardService implements ModerationDashboardServiceContract
{
    public function __construct(protected PostRepositoryContract $posts) {}

    /**
     * sla stats.

     *
     * @return ModerationSlaStats
     */
    public function slaStats(): ModerationSlaStats
    {
        $pendingCount = $this->posts->countPendingModeration();
        $oldest = $this->posts->oldestPendingModerationUpdatedAt();
        $ageMinutes = is_string($oldest) || $oldest instanceof \DateTimeInterface ? TypeCast::int(now()
            ->diffInMinutes($oldest)) : null;
        $slaMinutes = TypeCast::int(config('moderation.sla_minutes', 1440));

        return new ModerationSlaStats($pendingCount, $ageMinutes, $slaMinutes);
    }
}
