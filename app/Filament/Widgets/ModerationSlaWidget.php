<?php

namespace App\Filament\Widgets;

use App\Services\Contracts\ModerationDashboardServiceContract;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Виджет SLA модерации постов в админ-панели.
 *
 * @property-read ModerationDashboardServiceContract $moderationDashboard
 */
class ModerationSlaWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ModerationDashboardServiceContract $moderationDashboard;

    /**
     * Внедряет сервис SLA модерации.
     */
    public function boot(ModerationDashboardServiceContract $moderationDashboard): void
    {
        $this->moderationDashboard = $moderationDashboard;
    }

    /**
     * Возвращает stats.
     *
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $stats = $this->moderationDashboard->slaStats();

        return [Stat::make(__('admin.moderation_sla.pending'), (string) $stats->pendingCount)
            ->description(__('admin.moderation_sla.pending_description'))->color($stats
            ->pendingCount > 0 ? 'warning' : 'success'), Stat::make(__('admin.moderation_sla.oldest'), $stats
            ->oldestAgeMinutes !== null ? "{$stats->oldestAgeMinutes} min" : '—')
            ->description(__('admin.moderation_sla.sla_limit', ['minutes' => $stats->slaMinutes]))->color($stats
            ->isBreached() ? 'danger' : 'success')];
    }

    /**
     * Проверяет возможность view.

     *
     * @return bool
     */
    public static function canView(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->can('posts.moderate');
    }
}
