<?php

namespace App\Repositories\Contracts;

use App\DTO\SiteTemplateData;
use App\DTO\SiteTemplateThemeData;
use App\Models\SiteTemplate;
use App\Models\SiteTemplateTheme;
use Illuminate\Support\Collection;

/**
 * Контракт репозитория site template.
 */
interface SiteTemplateRepositoryContract
{
    /**
     * Находит by id.

     *
     * @return ?SiteTemplate
     */
    public function findById(int $id): ?SiteTemplate;

    /**
     * Возвращает активный шаблон с темами (межзапросный кэш).
     */
    public function findActiveTemplate(): ?SiteTemplate;

    /**
     * Сбрасывает кэш активного шаблона.
     */
    public function forgetActiveTemplateCache(): void;

    /**
     * Находит by slug.

     *
     * @return ?SiteTemplate
     */
    public function findBySlug(string $slug): ?SiteTemplate;

    /**
     * Возвращает default.

     *
     * @return ?SiteTemplate
     */
    public function getDefault(): ?SiteTemplate;

    /**
     * Возвращает active by flag.

     *
     * @return ?SiteTemplate
     */
    public function getActiveByFlag(): ?SiteTemplate;

    /**
     * Возвращает all.
     */
    /**
     * Возвращает all.
     */
    /**
     * Возвращает все шаблоны сайта.
     *
     * @return Collection<int, SiteTemplate>
     */
    public function getAll(): Collection;

    /**
     * Создаёт .
     *
     * @param  SiteTemplateData  $data  данные формы

     * @return SiteTemplate
     */
    public function create(SiteTemplateData $data): SiteTemplate;

    /**
     * Обновляет .
     *
     * @param  SiteTemplateData  $data  данные формы

     * @return SiteTemplate
     */
    public function update(SiteTemplate $template, SiteTemplateData $data): SiteTemplate;

    /**
     * Удаляет .
     */
    public function delete(SiteTemplate $template): void;

    /**
     * Возвращает active template id.

     *
     * @return ?int
     */
    public function getActiveTemplateId(): ?int;

    /**
     * set active template id.
     */
    public function setActiveTemplateId(?int $id): void;

    /**
     * mark as active.
     */
    public function markAsActive(SiteTemplate $template): void;

    /**
     * mark as default.
     */
    public function markAsDefault(SiteTemplate $template): void;

    /**
     * Находит theme by slug.

     *
     * @return ?SiteTemplateTheme
     */
    public function findThemeBySlug(SiteTemplate $template, string $slug): ?SiteTemplateTheme;

    /**
     * Возвращает default theme.

     *
     * @return ?SiteTemplateTheme
     */
    public function getDefaultTheme(SiteTemplate $template): ?SiteTemplateTheme;

    /**
     * Возвращает theme slugs.
     */
    /**
     * Возвращает theme slugs.
     */
    /**
     * Возвращает slug тем шаблона.
     *
     * @return Collection<int, string>
     */
    public function getThemeSlugs(SiteTemplate $template): Collection;

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
