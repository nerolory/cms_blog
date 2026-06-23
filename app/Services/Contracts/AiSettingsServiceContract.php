<?php

namespace App\Services\Contracts;

use App\DTO\AiSettingsData;

/**
 * Контракт сервиса ai settings.
 */
interface AiSettingsServiceContract
{
    /**
     * settings.
     *
     * @return AiSettingsData
     */
    public function settings(): AiSettingsData;

    /**
     * forget settings cache.
     */
    public function forgetSettingsCache(): void;
}
