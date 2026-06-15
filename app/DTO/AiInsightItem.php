<?php

namespace App\DTO;

use App\Enums\AiToolCode;
use Carbon\CarbonInterface;

/**
 * DTO ai insight item.

 *
 * @property-read AiToolCode $toolCode
 * @property-read string $label
 * @property-read string $summary
 * @property-read ?CarbonInterface $completedAt
 */
readonly class AiInsightItem
{
    public function __construct(public AiToolCode $toolCode, public string $label, public string $summary,
        public ?CarbonInterface $completedAt = null) {}
}
