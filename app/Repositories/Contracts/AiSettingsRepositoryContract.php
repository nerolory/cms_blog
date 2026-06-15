<?php

namespace App\Repositories\Contracts;

use App\DTO\AiSettingsData;
use App\Support\Ai\AiSettingKey;

/**
 * Контракт репозитория ai settings.
 */
interface AiSettingsRepositoryContract
{
    /**
     * Возвращает .
     *
     * @param  AiSettingKey  $key  ключ

     * @return ?string
     */
    public function get(AiSettingKey $key): ?string;

    /**
     * set.
     *
     * @param  AiSettingKey  $key  ключ
     */
    public function set(AiSettingKey $key, ?string $value): void;

    /**
     * Возвращает settings.

     *
     * @return AiSettingsData
     */
    public function getSettings(): AiSettingsData;
}
