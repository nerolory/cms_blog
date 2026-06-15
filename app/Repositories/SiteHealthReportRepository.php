<?php

namespace App\Repositories;

use App\DTO\SiteHealthCheckResult;
use App\DTO\SiteHealthReportData;
use App\Models\SiteHealthReport;
use App\Repositories\Contracts\SiteHealthReportRepositoryContract;
use Illuminate\Support\Collection;

/**
 * Репозиторий site health report.
 *
 * @property-read SiteHealthReport $report
 */
class SiteHealthReportRepository implements SiteHealthReportRepositoryContract
{
    public function __construct(protected SiteHealthReport $report) {}

    /**
     * store.
     *
     * @param  SiteHealthReportData  $data  данные формы

     * @return SiteHealthReport
     */
    public function store(SiteHealthReportData $data): SiteHealthReport
    {
        return $this->report->newQuery()->create([
            'context' => $data->context->value,
            'critical_count' => $data->criticalCount(),
            'warning_count' => $data->warningCount(),
            'passed_count' => $data->passedCount(),
            'results' => $data->results
                ->map(fn (SiteHealthCheckResult $result): array => $result->toArray())
                ->values()
                ->all(),
            'triggered_by' => $data->triggeredBy,
            'completed_at' => now(),
        ]);
    }

    /**
     * latest.
     */ /**
     * Возвращает последние отчёты о здоровье сайта.
     *
     * @return Collection<int, SiteHealthReport>
     */
    public function latest(int $limit = 10): Collection
    {
        return $this->report->newQuery()->with('triggeredBy')->whereNotNull('completed_at')->orderByDesc('completed_at')
            ->limit($limit)->get();
    }

    /**
     * Находит latest completed.

     *
     * @return ?SiteHealthReport
     */
    public function findLatestCompleted(): ?SiteHealthReport
    {
        return $this->report->newQuery()->whereNotNull('completed_at')->orderByDesc('completed_at')->first();
    }
}
