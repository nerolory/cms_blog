<?php

namespace App\Enums;

use App\Services\Contracts\SiteTemplateServiceContract;

/**
 * Перечисление user theme.
 */
enum UserTheme: string
{
    case Default = 'default';
    case Light = 'light';
    case Dark = 'dark';
    case Minimal = 'minimal';

    /**
     * values.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * layout view.

     *
     * @return string
     */
    public function layoutView(string $name = 'app'): string
    {
        return app(SiteTemplateServiceContract::class)->resolveLayout($name);
    }

    /**
     * bootstrap theme.

     *
     * @return ?string
     */
    public function bootstrapTheme(): ?string
    {
        return app(SiteTemplateServiceContract::class)->bootstrapThemeFor($this);
    }

    /**
     * body class.

     *
     * @return string
     */
    public function bodyClass(): string
    {
        return app(SiteTemplateServiceContract::class)->bodyClassFor($this);
    }

    /**
     * css entry.

     *
     * @return ?string
     */
    public function cssEntry(): ?string
    {
        return app(SiteTemplateServiceContract::class)->cssEntryFor($this);
    }
}
