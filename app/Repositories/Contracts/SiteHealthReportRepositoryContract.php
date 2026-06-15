<?php

namespace App\Repositories\Contracts;

use App\DTO\SiteHealthReportData;
use App\Models\SiteHealthReport;
use Illuminate\Support\Collection;

/**
 * Контракт репозитория site health report.
 */
interface SiteHealthReportRepositoryContract
{
    /**
     * store.
     *
     * @param  SiteHealthReportData  $data  данные формы

     * @return SiteHealthReport
     */
    public function store(SiteHealthReportData $data): SiteHealthReport;

    /**
     * latest.
     */
    /**
     * latest.
     */
    /**
     * Возвращает последние отчёты о здоровье сайта.
     *
     * @return Collection<int, SiteHealthReport>
     */
    public function latest(int $limit = 10): Collection;

    /**
     * Находит latest completed.

     *
     * @return ?SiteHealthReport
     */
    public function findLatestCompleted(): ?SiteHealthReport;
}
