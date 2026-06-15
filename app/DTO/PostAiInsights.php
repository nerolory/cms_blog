<?php

namespace App\DTO;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * DTO post ai insights.
 *
 * @property-read Collection<int, AiInsightItem> $items
 * @property-read int $commentCount
 * @property-read bool $canRequestOrder
 * @property-read bool $hasPendingOrder
 * @property-read ?CarbonInterface $cooldownUntil
 * @property-read ?int $tokensRequired
 */
readonly class PostAiInsights
{
    /**
     * @param  Collection<int, AiInsightItem>  $items
     */
    public function __construct(public Collection $items, public int $commentCount, public bool $canRequestOrder,
        public bool $hasPendingOrder, public ?CarbonInterface $cooldownUntil, public ?int $tokensRequired) {}

    /**
     * Проверяет наличие cache.

     *
     * @return bool
     */
    public function hasCache(): bool
    {
        return $this->items->isNotEmpty();
    }
}
