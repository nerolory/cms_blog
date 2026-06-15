<?php

namespace App\Contracts\SiteHealth;

use App\DTO\SiteHealthCheckResult;
use App\Enums\SiteHealthContext;

/**
 * Класс site health check.
 */
interface SiteHealthCheckContract
{
    /**
     * name.

     *
     * @return string
     */
    public function name(): string;

    /**
     * run.

     *
     * @return SiteHealthCheckResult
     */
    public function run(SiteHealthContext $context): SiteHealthCheckResult;
}
