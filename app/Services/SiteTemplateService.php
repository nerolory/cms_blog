<?php

namespace App\Services;

use App\DTO\SiteTemplateData;
use App\DTO\SiteTemplateThemeData;
use App\Enums\UserTheme;
use App\Exceptions\SiteTemplateException;
use App\Models\SiteTemplate;
use App\Models\SiteTemplateTheme;
use App\Repositories\Contracts\SiteTemplateRepositoryContract;
use App\Services\Contracts\SiteTemplateServiceContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;

/**
 * Сервис site template.
 *
 * @property-read SiteTemplateRepositoryContract $siteTemplates
 */
class SiteTemplateService implements SiteTemplateServiceContract
{
    private ?SiteTemplate $resolvedActiveTemplate = null;

    public function __construct(protected SiteTemplateRepositoryContract $siteTemplates) {}

    /**
     * resolve active template.

     *
     * @return SiteTemplate
     */
    public function resolveActiveTemplate(): SiteTemplate
    {
        if ($this->resolvedActiveTemplate instanceof SiteTemplate) {
            return $this->resolvedActiveTemplate;
        }
        $activeId = $this->siteTemplates->getActiveTemplateId();
        if ($activeId !== null) {
            $template = $this->siteTemplates->findById($activeId);
            if ($template instanceof SiteTemplate) {
                return $this->resolvedActiveTemplate = $template;
            }
        }
        $activeByFlag = $this->siteTemplates->getActiveByFlag();
        if ($activeByFlag instanceof SiteTemplate) {
            return $this->resolvedActiveTemplate = $activeByFlag;
        }
        $default = $this->siteTemplates->getDefault();
        if ($default instanceof SiteTemplate) {
            return $this->resolvedActiveTemplate = $default;
        }

        return $this->resolvedActiveTemplate = $this->fallbackTemplate();
    }

    /**
     * resolve layout.

     *
     * @return string
     */
    public function resolveLayout(string $name = 'app'): string
    {
        $template = $this->resolveActiveTemplate();
        $candidates = ["{$template->view_prefix}.layouts.{$name}", "themes.default.layouts.{$name}",
            'themes.default.layouts.app'];
        foreach (array_unique($candidates) as $view) {
            if (View::exists($view)) {
                return $view;
            }
        }

        return 'themes.default.layouts.app';
    }

    /**
     * resolve user theme.
     *
     * @param  ?string  $themeSlug  slug

     * @return UserTheme
     */
    public function resolveUserTheme(?string $themeSlug = null): UserTheme
    {
        $slug = $themeSlug ?? UserTheme::Default->value;
        $template = $this->resolveActiveTemplate();
        if ($template->id > 0) {
            $available = $this->siteTemplates->getThemeSlugs($template);
            if ($available->contains($slug)) {
                return UserTheme::tryFrom($slug) ?? UserTheme::Default;
            }
            $defaultTheme = $this->siteTemplates->getDefaultTheme($template);
            if ($defaultTheme instanceof SiteTemplateTheme) {
                return UserTheme::tryFrom($defaultTheme->slug) ?? UserTheme::Default;
            }
        }

        return UserTheme::tryFrom($slug) ?? UserTheme::Default;
    }

    /**
     * bootstrap theme for.

     *
     * @return ?string
     */
    public function bootstrapThemeFor(UserTheme $theme): ?string
    {
        $record = $this->resolveThemeRecord($theme);
        if ($record instanceof SiteTemplateTheme) {
            return $record->bootstrap_theme;
        }

        return match ($theme) {
            UserTheme::Light, UserTheme::Minimal => 'light',
            UserTheme::Dark => 'dark',
            UserTheme::Default => null,
        };
    }

    /**
     * body class for.

     *
     * @return string
     */
    public function bodyClassFor(UserTheme $theme): string
    {
        $record = $this->resolveThemeRecord($theme);
        if ($record instanceof SiteTemplateTheme) {
            return (string) ($record->body_class ?? '');
        }

        return match ($theme) {
            UserTheme::Minimal => 'theme-minimal',
            default => '',
        };
    }

    /**
     * css entry for.

     *
     * @return ?string
     */
    public function cssEntryFor(UserTheme $theme): ?string
    {
        $record = $this->resolveThemeRecord($theme);

        return $record?->css_entry;
    }

    /**
     * {@inheritdoc}
     */
    /**
     * Возвращает slug доступных тем.
     *
     * @return Collection<int, string>
     */
    public function availableThemeSlugs(): Collection
    {
        $template = $this->resolveActiveTemplate();
        if ($template->id > 0) {
            $slugs = $this->siteTemplates->getThemeSlugs($template);
            if ($slugs->isNotEmpty()) {
                return $slugs;
            }
        }

        return collect(UserTheme::values());
    }

    /**
     * Создаёт template.
     *
     * @param  SiteTemplateData  $data  данные формы

     * @return SiteTemplate
     */
    public function createTemplate(SiteTemplateData $data): SiteTemplate
    {
        return $this->siteTemplates->create($data);
    }

    /**
     * Обновляет template.
     *
     * @param  SiteTemplateData  $data  данные формы

     * @return SiteTemplate
     */
    public function updateTemplate(SiteTemplate $template, SiteTemplateData $data): SiteTemplate
    {
        return $this->siteTemplates->update($template, $data);
    }

    /**
     * Удаляет template.
     */
    public function deleteTemplate(SiteTemplate $template): void
    {
        if ($template->is_default) {
            throw SiteTemplateException::cannotDeleteDefault();
        }
        if ($template->is_active) {
            throw SiteTemplateException::cannotDeleteActive();
        }
        $this->siteTemplates->delete($template);
        $this->resetResolvedTemplate();
    }

    /**
     * set active template.
     */
    public function setActiveTemplate(SiteTemplate $template): void
    {
        $this->siteTemplates->markAsActive($template);
        $this->resetResolvedTemplate();
    }

    /**
     * Создаёт theme.
     *
     * @param  SiteTemplateThemeData  $data  данные формы

     * @return SiteTemplateTheme
     */
    public function createTheme(SiteTemplate $template, SiteTemplateThemeData $data): SiteTemplateTheme
    {
        return $this->siteTemplates->createTheme($template, $data);
    }

    /**
     * Обновляет theme.
     *
     * @param  SiteTemplateThemeData  $data  данные формы

     * @return SiteTemplateTheme
     */
    public function updateTheme(SiteTemplateTheme $theme, SiteTemplateThemeData $data): SiteTemplateTheme
    {
        return $this->siteTemplates->updateTheme($theme, $data);
    }

    /**
     * Удаляет theme.
     */
    public function deleteTheme(SiteTemplateTheme $theme): void
    {
        if ($theme->is_default) {
            throw SiteTemplateException::cannotDeleteDefaultTheme();
        }
        $this->siteTemplates->deleteTheme($theme);
    }

    private function resolveThemeRecord(UserTheme $theme): ?SiteTemplateTheme
    {
        $template = $this->resolveActiveTemplate();
        if ($template->id <= 0) {
            return null;
        }

        return $this->siteTemplates->findThemeBySlug($template, $theme->value);
    }

    private function fallbackTemplate(): SiteTemplate
    {
        $template = new SiteTemplate(['slug' => 'default', 'name' => 'Default', 'view_prefix' => 'themes.default',
            'is_active' => true, 'is_default' => true]);
        $template->id = 0;

        return $template;
    }

    private function resetResolvedTemplate(): void
    {
        $this->resolvedActiveTemplate = null;
    }
}
