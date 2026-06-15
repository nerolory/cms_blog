<?php

namespace App\Support;

use App\Enums\UserTheme;
use App\Models\User;
use App\Services\Contracts\SiteTemplateServiceContract;

/**
 * Вспомогательный класс theme.
 */
class Theme
{
    /**
     * current.

     *
     * @return UserTheme
     */
    public static function current(): UserTheme
    {
        $user = auth()->user();
        $themeSlug = $user instanceof User ? $user->theme : null;

        return app(SiteTemplateServiceContract::class)->resolveUserTheme($themeSlug);
    }

    /**
     * layout.

     *
     * @return string
     */
    public static function layout(string $name = 'app'): string
    {
        return app(SiteTemplateServiceContract::class)->resolveLayout($name);
    }
}
