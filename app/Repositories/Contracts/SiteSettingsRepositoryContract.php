<?php

namespace App\Repositories\Contracts;

use App\DTO\SiteSettingsData;

interface SiteSettingsRepositoryContract
{
    public function getSiteSettings(): SiteSettingsData;

    public function saveSiteSettings(SiteSettingsData $data): void;
}
