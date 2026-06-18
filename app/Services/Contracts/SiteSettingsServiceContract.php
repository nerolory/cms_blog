<?php

namespace App\Services\Contracts;

use App\DTO\SiteSettingsData;

interface SiteSettingsServiceContract
{
    public function siteName(): string;

    public function settings(): SiteSettingsData;

    public function saveSettings(SiteSettingsData $data): SiteSettingsData;
}
