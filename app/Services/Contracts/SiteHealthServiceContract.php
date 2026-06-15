<?php

namespace App\Services\Contracts;

use App\DTO\SiteHealthReportData;
use App\Enums\SiteHealthProfile;
use App\Models\SiteHealthReport;
use Illuminate\Support\Collection;

/**
 * Контракт сервиса site health.
 */
interface SiteHealthServiceContract
{
    /**
     * run checks.
     *
     * @param  ?int  $triggeredBy  by

     * @return SiteHealthReportData
     */
    public function runChecks(?int $triggeredBy = null,
        SiteHealthProfile $profile = SiteHealthProfile::Default): SiteHealthReportData;

    /**
     * persist report.
     *
     * @param  SiteHealthReportData  $data  данные формы

     * @return SiteHealthReport
     */
    public function persistReport(SiteHealthReportData $data): SiteHealthReport;

    /**
     * history.
     */
    /**
     * history.
     */
    /**
     * Метод history.
     *
     * @return Collection<int, SiteHealthReport>
     */
    public function history(int $limit = 10): Collection;

    /**
     * latest report.

     *
     * @return ?SiteHealthReport
     */
    public function latestReport(): ?SiteHealthReport;
}
