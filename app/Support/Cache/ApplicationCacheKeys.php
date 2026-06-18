<?php

namespace App\Support\Cache;

/**
 * Ключи межзапросного кэша приложения.
 */
final class ApplicationCacheKeys
{
    public const MAIL_SETTINGS = 'mail:settings';

    public const SITE_ACTIVE_TEMPLATE_BUNDLE = 'site:active_template_bundle';

    public const SEO_LISTING_LATEST_UPDATED_AT = 'seo:listing:latest_updated_at';

    public const SITE_BRANDING = 'site:branding';

    public static function postCommentSection(int $postId): string
    {
        return 'post:'.$postId.':comment_section:v2';
    }

    public static function postAiToolResults(int $postId): string
    {
        return 'post:'.$postId.':ai_tool_results:v1';
    }

    public static function postEngagementVersion(int $postId): string
    {
        return 'post:'.$postId.':engagement:version';
    }

    public const AI_SETTINGS = 'ai:settings';

    public static function postAiInsightOrderState(int $postId, int $userId): string
    {
        return 'post:'.$postId.':ai_order_state:'.$userId.':v1';
    }

    public static function userTokenBalance(int $userId): string
    {
        return 'user:'.$userId.':token_balance';
    }
}
