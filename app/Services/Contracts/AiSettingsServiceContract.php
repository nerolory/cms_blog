<?php

namespace App\Services\Contracts;

use App\DTO\AiSettingsData;

interface AiSettingsServiceContract
{
    public function settings(): AiSettingsData;

    public function forgetSettingsCache(): void;
}
