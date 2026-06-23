<?php

namespace App\Services\Contracts;

use App\DTO\SiteSettingsData;

/**
 * Контракт сервиса site settings.
 */
interface SiteSettingsServiceContract
{
    /**
     * site name.
     *
     * @return string
     */
    public function siteName(): string;

    /**
     * settings.
     *
     * @return SiteSettingsData
     */
    public function settings(): SiteSettingsData;

    /**
     * save settings.
     *
     * @param  SiteSettingsData  $data
     * @return SiteSettingsData
     */
    public function saveSettings(SiteSettingsData $data): SiteSettingsData;
}
