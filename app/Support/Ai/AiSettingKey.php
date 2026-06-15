<?php

namespace App\Support\Ai;

/**
 * Keys for AI settings in the settings table.
 */
enum AiSettingKey: string
{
    case CooldownDays = 'ai.cooldown_days';
    case AutoAnalysisThreshold = 'ai.auto_analysis_comment_threshold';
    case TokensMinimum = 'ai.tokens.minimum';
    case TokensPerComment = 'ai.tokens.per_comment';
}
