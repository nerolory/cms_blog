<?php

namespace App\Services\Contracts;

use App\DTO\SiteTemplateData;
use App\DTO\SiteTemplateThemeData;
use App\Enums\UserTheme;
use App\Models\SiteTemplate;
use App\Models\SiteTemplateTheme;
use Illuminate\Support\Collection;

/**
 * Контракт сервиса site template.
 */
interface SiteTemplateServiceContract
{
    /**
     * resolve active template.

     *
     * @return SiteTemplate
     */
    public function resolveActiveTemplate(): SiteTemplate;

    /**
     * resolve layout.

     *
     * @return string
     */
    public function resolveLayout(string $name = 'app'): string;

    /**
     * resolve user theme.
     *
     * @param  ?string  $themeSlug  slug

     * @return UserTheme
     */
    public function resolveUserTheme(?string $themeSlug = null): UserTheme;

    /**
     * bootstrap theme for.

     *
     * @return ?string
     */
    public function bootstrapThemeFor(UserTheme $theme): ?string;

    /**
     * body class for.

     *
     * @return string
     */
    public function bodyClassFor(UserTheme $theme): string;

    /**
     * css entry for.

     *
     * @return ?string
     */
    public function cssEntryFor(UserTheme $theme): ?string;

    /**
     * available theme slugs.
     */
    /**
     * available theme slugs.
     */
    /**
     * Возвращает slug доступных тем.
     *
     * @return Collection<int, string>
     */
    public function availableThemeSlugs(): Collection;

    /**
     * Создаёт template.
     *
     * @param  SiteTemplateData  $data  данные формы

     * @return SiteTemplate
     */
    public function createTemplate(SiteTemplateData $data): SiteTemplate;

    /**
     * Обновляет template.
     *
     * @param  SiteTemplateData  $data  данные формы

     * @return SiteTemplate
     */
    public function updateTemplate(SiteTemplate $template, SiteTemplateData $data): SiteTemplate;

    /**
     * Удаляет template.
     */
    public function deleteTemplate(SiteTemplate $template): void;

    /**
     * set active template.
     */
    public function setActiveTemplate(SiteTemplate $template): void;

    /**
     * Создаёт theme.
     *
     * @param  SiteTemplateThemeData  $data  данные формы

     * @return SiteTemplateTheme
     */
    public function createTheme(SiteTemplate $template, SiteTemplateThemeData $data): SiteTemplateTheme;

    /**
     * Обновляет theme.
     *
     * @param  SiteTemplateThemeData  $data  данные формы

     * @return SiteTemplateTheme
     */
    public function updateTheme(SiteTemplateTheme $theme, SiteTemplateThemeData $data): SiteTemplateTheme;

    /**
     * Удаляет theme.
     */
    public function deleteTheme(SiteTemplateTheme $theme): void;
}
