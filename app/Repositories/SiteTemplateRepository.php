<?php

namespace App\Repositories;

use App\DTO\SiteTemplateData;
use App\DTO\SiteTemplateThemeData;
use App\Enums\UserTheme;
use App\Models\Setting;
use App\Models\SiteTemplate;
use App\Models\SiteTemplateTheme;
use App\Repositories\Contracts\SiteTemplateRepositoryContract;
use App\Support\Site\SiteTemplateSettingKey;
use App\Support\TypeCast;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Репозиторий site template.
 *
 * @property-read SiteTemplate $siteTemplate
 * @property-read SiteTemplateTheme $siteTemplateTheme
 * @property-read Setting $setting
 */
class SiteTemplateRepository implements SiteTemplateRepositoryContract
{
    public function __construct(protected SiteTemplate $siteTemplate, protected SiteTemplateTheme $siteTemplateTheme,
        protected Setting $setting) {}

    /**
     * Находит by id.

     *
     * @return ?SiteTemplate
     */
    public function findById(int $id): ?SiteTemplate
    {
        return $this->siteTemplate->newQuery()->with('themes')->find($id);
    }

    /**
     * Находит by slug.

     *
     * @return ?SiteTemplate
     */
    public function findBySlug(string $slug): ?SiteTemplate
    {
        return $this->siteTemplate->newQuery()->with('themes')->where('slug', $slug)->first();
    }

    /**
     * Возвращает default.

     *
     * @return ?SiteTemplate
     */
    public function getDefault(): ?SiteTemplate
    {
        if (! $this->templatesTableExists()) {
            return null;
        }

        return $this->siteTemplate->newQuery()->with('themes')->where('is_default', true)->first();
    }

    /**
     * Возвращает active by flag.

     *
     * @return ?SiteTemplate
     */
    public function getActiveByFlag(): ?SiteTemplate
    {
        if (! $this->templatesTableExists()) {
            return null;
        }

        return $this->siteTemplate->newQuery()->with('themes')->where('is_active', true)->first();
    }

    /**
     * {@inheritdoc}
     */ /**
     * Возвращает все шаблоны сайта.
     *
     * @return Collection<int, SiteTemplate>
     */
    public function getAll(): Collection
    {
        return $this->siteTemplate->newQuery()->with('themes')->orderBy('name')->get();
    }

    /**
     * Создаёт .
     *
     * @param  SiteTemplateData  $data  данные формы

     * @return SiteTemplate
     */
    public function create(SiteTemplateData $data): SiteTemplate
    {
        $template = $this->siteTemplate->newQuery()->create(['slug' => $data->slug, 'name' => $data->name,
            'view_prefix' => $data->viewPrefix, 'is_active' => false, 'is_default' => $data->isDefault]);
        if ($data->isDefault) {
            $this->clearDefaultExcept($template->id);
        }

        return $template->load('themes');
    }

    /**
     * Обновляет .
     *
     * @param  SiteTemplateData  $data  данные формы

     * @return SiteTemplate
     */
    public function update(SiteTemplate $template, SiteTemplateData $data): SiteTemplate
    {
        $template->updateOrFail(['slug' => $data->slug, 'name' => $data->name, 'view_prefix' => $data->viewPrefix,
            'is_default' => $data->isDefault]);
        if ($data->isDefault) {
            $this->clearDefaultExcept($template->id);
        }

        return $template->fresh(['themes']) ?? $template;
    }

    /**
     * Удаляет .
     */
    public function delete(SiteTemplate $template): void
    {
        $template->deleteOrFail();
    }

    /**
     * Возвращает active template id.

     *
     * @return ?int
     */
    public function getActiveTemplateId(): ?int
    {
        if (! $this->settingsTableExists()) {
            return null;
        }
        $value = $this->setting->newQuery()->find(SiteTemplateSettingKey::ACTIVE_TEMPLATE_ID)?->value;
        if ($value === null || $value === '') {
            return null;
        }

        return TypeCast::int($value);
    }

    /**
     * set active template id.
     */
    public function setActiveTemplateId(?int $id): void
    {
        if (! $this->settingsTableExists()) {
            return;
        }
        if ($id === null) {
            $this->setting->newQuery()->where('key', SiteTemplateSettingKey::ACTIVE_TEMPLATE_ID)->delete();

            return;
        }
        $this->setting->newQuery()->updateOrCreate(['key' => SiteTemplateSettingKey::ACTIVE_TEMPLATE_ID],
            ['value' => (string) $id]);
    }

    /**
     * mark as active.
     */
    public function markAsActive(SiteTemplate $template): void
    {
        $this->siteTemplate->newQuery()->whereKeyNot($template->id)->update(['is_active' => false]);
        $template->updateOrFail(['is_active' => true]);
        $this->setActiveTemplateId($template->id);
    }

    /**
     * mark as default.
     */
    public function markAsDefault(SiteTemplate $template): void
    {
        $this->clearDefaultExcept($template->id);
        $template->updateOrFail(['is_default' => true]);
    }

    /**
     * Находит theme by slug.

     *
     * @return ?SiteTemplateTheme
     */
    public function findThemeBySlug(SiteTemplate $template, string $slug): ?SiteTemplateTheme
    {
        return $this->siteTemplateTheme->newQuery()->where('site_template_id', $template->id)->where('slug',
            $slug)->first();
    }

    /**
     * Возвращает default theme.

     *
     * @return ?SiteTemplateTheme
     */
    public function getDefaultTheme(SiteTemplate $template): ?SiteTemplateTheme
    {
        return $this->siteTemplateTheme->newQuery()->where('site_template_id', $template->id)->where('is_default',
            true)->first() ?? $this->siteTemplateTheme->newQuery()->where('site_template_id',
                $template->id)->orderBy('id')->first();
    }

    /**
     * {@inheritdoc}
     */
    /**
     * Возвращает slug тем шаблона.
     *
     * @return Collection<int, string>
     */
    public function getThemeSlugs(SiteTemplate $template): Collection
    {
        if (! $this->templatesTableExists() || $template->id <= 0) {
            return collect(UserTheme::values());
        }

        return $this->siteTemplateTheme->newQuery()->where('site_template_id', $template->id)->orderBy('id')
            ->pluck('slug')->map(fn (mixed $slug): string => TypeCast::string($slug))->values();
    }

    /**
     * Создаёт theme.
     *
     * @param  SiteTemplateThemeData  $data  данные формы

     * @return SiteTemplateTheme
     */
    public function createTheme(SiteTemplate $template, SiteTemplateThemeData $data): SiteTemplateTheme
    {
        $theme = $this->siteTemplateTheme->newQuery()->create(['site_template_id' => $template->id,
            'slug' => $data->slug, 'name' => $data->name, 'bootstrap_theme' => $data->bootstrapTheme,
            'body_class' => $data->bodyClass, 'css_entry' => $data->cssEntry, 'is_default' => $data->isDefault]);
        if ($data->isDefault) {
            $this->clearDefaultThemeExcept($template->id, $theme->id);
        }

        return $theme;
    }

    /**
     * Обновляет theme.
     *
     * @param  SiteTemplateThemeData  $data  данные формы

     * @return SiteTemplateTheme
     */
    public function updateTheme(SiteTemplateTheme $theme, SiteTemplateThemeData $data): SiteTemplateTheme
    {
        $theme->updateOrFail(['slug' => $data->slug, 'name' => $data->name, 'bootstrap_theme' => $data->bootstrapTheme,
            'body_class' => $data->bodyClass, 'css_entry' => $data->cssEntry, 'is_default' => $data->isDefault]);
        if ($data->isDefault) {
            $this->clearDefaultThemeExcept($theme->site_template_id, $theme->id);
        }

        return $theme->fresh() ?? $theme;
    }

    /**
     * Удаляет theme.
     */
    public function deleteTheme(SiteTemplateTheme $theme): void
    {
        $theme->deleteOrFail();
    }

    private function clearDefaultExcept(int $templateId): void
    {
        $this->siteTemplate->newQuery()->whereKeyNot($templateId)->update(['is_default' => false]);
    }

    private function clearDefaultThemeExcept(int $templateId, int $themeId): void
    {
        $this->siteTemplateTheme->newQuery()->where('site_template_id',
            $templateId)->whereKeyNot($themeId)->update(['is_default' => false]);
    }

    private function settingsTableExists(): bool
    {
        return Schema::hasTable('settings');
    }

    private function templatesTableExists(): bool
    {
        return Schema::hasTable('site_templates');
    }
}
