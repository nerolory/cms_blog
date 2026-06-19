<?php

namespace App\Repositories\Contracts;

use App\DTO\SiteSettingsData;

/**
 * Контракт репозитория site settings.
 */
interface SiteSettingsRepositoryContract
{
    /**
     * Возвращает site settings.
     *
     * @return SiteSettingsData
     */
    public function getSiteSettings(): SiteSettingsData;

    /**
     * save site settings.
     *
     * @param  SiteSettingsData  $data
     */
    public function saveSiteSettings(SiteSettingsData $data): void;
}
