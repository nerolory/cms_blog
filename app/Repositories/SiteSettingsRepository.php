<?php

namespace App\Repositories;

use App\DTO\SiteSettingsData;
use App\Models\Setting;
use App\Repositories\Contracts\SiteSettingsRepositoryContract;
use App\Support\Site\SiteSettingKey;
use App\Support\TypeCast;

/**
 * Репозиторий настроек брендинга сайта.
 *
 * @property-read Setting $setting
 */
class SiteSettingsRepository implements SiteSettingsRepositoryContract
{
    public function __construct(protected Setting $setting) {}

    /**
     * {@inheritDoc}
     */
    public function getSiteSettings(): SiteSettingsData
    {
        $stored = $this->setting->newQuery()->find(SiteSettingKey::SITE_NAME)?->value;

        return new SiteSettingsData(
            siteName: TypeCast::string($stored ?? config('app.name', 'Laravel')),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function saveSiteSettings(SiteSettingsData $data): void
    {
        $name = trim($data->siteName);
        if ($name === '') {
            $this->setting->newQuery()->where('key', SiteSettingKey::SITE_NAME)->delete();

            return;
        }

        $this->setting->newQuery()->updateOrCreate(
            ['key' => SiteSettingKey::SITE_NAME],
            ['value' => $name],
        );
    }
}
