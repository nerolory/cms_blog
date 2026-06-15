<?php

namespace App\Services;

use App\Contracts\SiteHealth\SiteHealthCheckContract;
use App\DTO\SiteHealthReportData;
use App\Enums\SiteHealthContext;
use App\Enums\SiteHealthProfile;
use App\Models\SiteHealthReport;
use App\Repositories\Contracts\SiteHealthReportRepositoryContract;
use App\Services\Contracts\SiteHealthServiceContract;
use Illuminate\Support\Collection;

/**
 * Сервис site health.
 *
 * @property-read SiteHealthReportRepositoryContract $reports
 * @property-read iterable<int, SiteHealthCheckContract> $checks
 */
class SiteHealthService implements SiteHealthServiceContract
{
    /**
     * @param  iterable<int, SiteHealthCheckContract>  $checks
     */
    public function __construct(protected SiteHealthReportRepositoryContract $reports, protected iterable $checks) {}

    /**
     * run checks.
     *
     * @param  ?int  $triggeredBy  by

     * @return SiteHealthReportData
     */
    public function runChecks(?int $triggeredBy = null,
        SiteHealthProfile $profile = SiteHealthProfile::Default): SiteHealthReportData
    {
        $context = SiteHealthContext::fromConfig();
        $results = collect();
        foreach ($this->checksForProfile($profile) as $check) {
            $results->push($check->run($context));
        }

        return new SiteHealthReportData(context: $context, results: $results, triggeredBy: $triggeredBy);
    }

    /**
     * persist report.
     *
     * @param  SiteHealthReportData  $data  данные формы

     * @return SiteHealthReport
     */
    public function persistReport(SiteHealthReportData $data): SiteHealthReport
    {
        return $this->reports->store($data);
    }

    /**
     * history.
     */ /**
     * Возвращает историю отчётов.
     *
     * @return Collection<int, SiteHealthReport>
     */
    public function history(int $limit = 10): Collection
    {
        return $this->reports->latest($limit);
    }

    /**
     * latest report.

     *
     * @return ?SiteHealthReport
     */
    public function latestReport(): ?SiteHealthReport
    {
        return $this->reports->findLatestCompleted();
    }

    /**
     * @return list<SiteHealthCheckContract>
     */
    private function checksForProfile(SiteHealthProfile $profile): array
    {
        $allowed = $profile->checkNames();
        $selected = [];
        foreach ($this->checks as $check) {
            if (in_array($check->name(), $allowed, true)) {
                $selected[] = $check;
            }
        }

        return $selected;
    }
}
