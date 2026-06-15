<?php

namespace App\DTO;

/**
 * DTO ai job dispatch result.

 *
 * @property-read string $jobId
 * @property-read string $status
 */
readonly class AiJobDispatchResult extends AbstractData
{
    public function __construct(public string $jobId, public string $status) {}
}
