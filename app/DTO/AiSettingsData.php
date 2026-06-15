<?php

namespace App\DTO;

/**
 * DTO ai settings.

 *
 * @property-read int $cooldownDays
 * @property-read int $autoAnalysisCommentThreshold
 * @property-read int $tokensMinimum
 * @property-read int $tokensPerComment
 */
readonly class AiSettingsData
{
    public function __construct(public int $cooldownDays, public int $autoAnalysisCommentThreshold,
        public int $tokensMinimum, public int $tokensPerComment) {}

    /**
     * Стоимость анализа по числу комментариев и тарифу.
     *
     * @return int
     */
    public function tokensRequiredForComments(int $commentCount): int
    {
        return max($this->tokensMinimum, $commentCount * $this->tokensPerComment);
    }
}
