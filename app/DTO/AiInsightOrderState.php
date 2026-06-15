<?php

namespace App\DTO;

use App\Models\AiAnalysisOrder;

/**
 * Состояние заказов AI-анализа для блока insights на странице поста.

 *
 * @property-read ?AiAnalysisOrder $cooldown
 * @property-read ?AiAnalysisOrder $pending
 */
readonly class AiInsightOrderState
{
    public function __construct(
        public ?AiAnalysisOrder $cooldown,
        public ?AiAnalysisOrder $pending,
    ) {}
}
