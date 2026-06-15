<?php

namespace App\DTO;

use App\Enums\SiteHealthContext;
use Illuminate\Support\Collection;

/**
 * DTO site health report.

 *
 * @property-read SiteHealthContext $context
 * @property-read Collection<int, SiteHealthCheckResult> $results
 * @property-read ?int $triggeredBy
 */
readonly class SiteHealthReportData
{
    /**
     * @param  Collection<int, SiteHealthCheckResult>  $results
     */
    public function __construct(public SiteHealthContext $context, public Collection $results,
        public ?int $triggeredBy = null) {}

    /**
     * critical count.

     *
     * @return int
     */
    public function criticalCount(): int
    {
        return $this->results->filter(fn (SiteHealthCheckResult $result): bool => $result->severity
            ->value === 'critical')->count();
    }

    /**
     * warning count.

     *
     * @return int
     */
    public function warningCount(): int
    {
        return $this->results->filter(fn (SiteHealthCheckResult $result): bool => $result->severity
            ->value === 'warning')->count();
    }

    /**
     * passed count.

     *
     * @return int
     */
    public function passedCount(): int
    {
        return $this->results->filter(fn (SiteHealthCheckResult $result): bool => $result->severity->value === 'passed')
            ->count();
    }
}
