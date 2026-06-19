<?php

namespace App\Support\Http;

use Illuminate\Http\Request;

/**
 * Определяет fetch-запросы engagement SPA (реакции, комментарии без reload).
 */
final class EngagementSpaRequest
{
    /**
     * matches.
     *
     * @param  Request  $request
     * @return bool
     */
    public static function matches(Request $request): bool
    {
        return $request->expectsJson()
            || $request->wantsJson()
            || $request->header('X-Engagement-Spa') === '1';
    }
}
