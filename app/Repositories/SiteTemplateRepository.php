<?php

namespace App\Repositories;

use App\DTO\SiteTemplateData;
use App\DTO\SiteTemplateThemeData;
use App\Enums\UserTheme;
use App\Models\Setting;
use App\Models\SiteTemplate;
use App\Models\SiteTemplateTheme;
use App\Repositories\Contracts\SiteTemplateRepositoryContract;
use App\Support\Database\SchemaInspector;
use App\Support\Site\SiteTemplateSettingKey;
use App\Support\TypeCast;
use App\Support\Cache\ApplicationCacheKeys;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Репозиторий site template.
 *
 * @property-read SiteTemplate $siteTemplate
 * @property-read SiteTemplateTheme $siteTemplateTheme
 * @property-read Setting $setting
 */
class SiteTemplateRepository implements SiteTemplateRepositoryContract
{
    private const BUNDLE_CACHE_TTL_SECONDS = 3600;

    private bool $activeTemplateIdResolved = false;

    private ?int $cachedActiveTemplateId = null;

    /** @var array<int, SiteTemplate|null> */
    private array $templateByIdCache = [];

    public function __construct(protected SiteTemplate $siteTemplate, protected SiteTemplateTheme $siteTemplateTheme,
        protected Setting $setting) {}

    /**
     * Находит by id.

     *
     * @return ?SiteTemplate
     */
    public function findById(int $id): ?SiteTemplate
    {
        if (array_key_exists($id, $this->templateByIdCache)) {
            return $this->templateByIdCache[$id];
        }

        return $this->templateByIdCache[$id] = $this->siteTemplate->newQuery()->with('themes')->find($id);
    }

    /**
     * Возвращает активный шаблон с темами из межзапросного кэша.
     */
    public function findActiveTemplate(): ?SiteTemplate
    {
        /** @var ?array<string, mixed> $bundle */
        $bundle = Cache::remember(ApplicationCacheKeys::SITE_ACTIVE_TEMPLATE_BUNDLE, self::BUNDLE_CACHE_TTL_SECONDS,
            fn (): ?array => $this->loadActiveTemplateBundleFromDatabase());

        if ($bundle === null) {
            return null;
        }

        $template = $this->hydrateTemplateFromBundle($bundle);
        $this->templateByIdCache[$template->id] = $template;
        $this->activeTemplateIdResolved = true;
        $this->cachedActiveTemplateId = $template->id;

        return $template;
    }

    /**
     * Сбрасывает кэш активного шаблона.
     */
    public function forgetActiveTemplateCache(): void
    {
        Cache::forget(ApplicationCacheKeys::SITE_ACTIVE_TEMPLATE_BUNDLE);
        $this->templateByIdCache = [];
        $this->activeTemplateIdResolved = false;
        $this->cachedActiveTemplateId = null;
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
        $this->forgetActiveTemplateCache();

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
        $this->forgetActiveTemplateCache();

        return $template->fresh(['themes']) ?? $template;
    }

    /**
     * Удаляет .
     */
    public function delete(SiteTemplate $template): void
    {
        $template->deleteOrFail();
        $this->forgetActiveTemplateCache();
    }

    /**
     * Возвращает active template id.

     *
     * @return ?int
     */
    public function getActiveTemplateId(): ?int
    {
        if ($this->activeTemplateIdResolved) {
            return $this->cachedActiveTemplateId;
        }
        $template = $this->findActiveTemplate();
        $this->activeTemplateIdResolved = true;

        return $this->cachedActiveTemplateId = $template?->id;
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
            $this->forgetActiveTemplateCache();

            return;
        }
        $this->setting->newQuery()->updateOrCreate(['key' => SiteTemplateSettingKey::ACTIVE_TEMPLATE_ID],
            ['value' => (string) $id]);
        $this->forgetActiveTemplateCache();
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
        if ($template->relationLoaded('themes')) {
            $theme = $template->themes->firstWhere('slug', $slug);

            return $theme instanceof SiteTemplateTheme ? $theme : null;
        }

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

        if ($template->relationLoaded('themes')) {
            return $template->themes->pluck('slug')->map(fn (mixed $slug): string => TypeCast::string($slug))->values();
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
        $this->forgetActiveTemplateCache();

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
        $this->forgetActiveTemplateCache();

        return $theme->fresh() ?? $theme;
    }

    /**
     * Удаляет theme.
     */
    public function deleteTheme(SiteTemplateTheme $theme): void
    {
        $theme->deleteOrFail();
        $this->forgetActiveTemplateCache();
    }

    /**
     * @return ?array<string, mixed>
     */
    private function loadActiveTemplateBundleFromDatabase(): ?array
    {
        if (! $this->templatesTableExists()) {
            return null;
        }
        $template = null;
        if ($this->settingsTableExists()) {
            $value = $this->setting->newQuery()->find(SiteTemplateSettingKey::ACTIVE_TEMPLATE_ID)?->value;
            if ($value !== null && $value !== '') {
                $template = $this->siteTemplate->newQuery()->with('themes')->find(TypeCast::int($value));
            }
        }
        $template ??= $this->siteTemplate->newQuery()->with('themes')->where('is_active', true)->first();
        $template ??= $this->siteTemplate->newQuery()->with('themes')->where('is_default', true)->first();
        if (! $template instanceof SiteTemplate) {
            return null;
        }

        return $this->serializeTemplateBundle($template);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeTemplateBundle(SiteTemplate $template): array
    {
        return [
            'id' => $template->id,
            'slug' => $template->slug,
            'name' => $template->name,
            'view_prefix' => $template->view_prefix,
            'is_active' => $template->is_active,
            'is_default' => $template->is_default,
            'themes' => $template->themes->map(static fn (SiteTemplateTheme $theme): array => [
                'id' => $theme->id,
                'site_template_id' => $theme->site_template_id,
                'slug' => $theme->slug,
                'name' => $theme->name,
                'bootstrap_theme' => $theme->bootstrap_theme,
                'body_class' => $theme->body_class,
                'css_entry' => $theme->css_entry,
                'is_default' => $theme->is_default,
            ])->values()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $bundle
     */
    private function hydrateTemplateFromBundle(array $bundle): SiteTemplate
    {
        $template = new SiteTemplate([
            'slug' => TypeCast::string($bundle['slug'] ?? ''),
            'name' => TypeCast::string($bundle['name'] ?? ''),
            'view_prefix' => TypeCast::string($bundle['view_prefix'] ?? 'themes.default'),
            'is_active' => TypeCast::bool($bundle['is_active'] ?? false),
            'is_default' => TypeCast::bool($bundle['is_default'] ?? false),
        ]);
        $template->id = TypeCast::int($bundle['id'] ?? 0);
        $template->exists = true;
        /** @var list<array<string, mixed>> $themes */
        $themes = TypeCast::array($bundle['themes'] ?? []);
        $themeModels = collect($themes)->map(function (array $row): SiteTemplateTheme {
            $theme = new SiteTemplateTheme([
                'site_template_id' => TypeCast::int($row['site_template_id'] ?? 0),
                'slug' => TypeCast::string($row['slug'] ?? ''),
                'name' => TypeCast::string($row['name'] ?? ''),
                'bootstrap_theme' => TypeCast::nullableString($row['bootstrap_theme'] ?? null),
                'body_class' => TypeCast::nullableString($row['body_class'] ?? null),
                'css_entry' => TypeCast::nullableString($row['css_entry'] ?? null),
                'is_default' => TypeCast::bool($row['is_default'] ?? false),
            ]);
            $theme->id = TypeCast::int($row['id'] ?? 0);
            $theme->exists = true;

            return $theme;
        });
        $template->setRelation('themes', $themeModels);

        return $template;
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
        return SchemaInspector::hasSettingsTable();
    }

    private function templatesTableExists(): bool
    {
        return SchemaInspector::hasSiteTemplatesTable();
    }
}
