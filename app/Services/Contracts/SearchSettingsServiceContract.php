<?php

namespace App\Services\Contracts;

use App\DTO\SearchSettingsData;
use Illuminate\Support\Collection;

/**
 * Контракт сервиса search settings.
 */
interface SearchSettingsServiceContract
{
    /**
     * settings.

     *
     * @return SearchSettingsData
     */
    public function settings(): SearchSettingsData;

    /**
     * save settings.
     *
     * @param  SearchSettingsData  $data  данные формы

     * @return SearchSettingsData
     */
    public function saveSettings(SearchSettingsData $data): SearchSettingsData;

    /**
     * available drivers.
     */
    /**
     * available drivers.
     */
    /**
     * Возвращает доступные драйверы поиска.
     *
     * @return Collection<string, string>
     */
    public function availableDrivers(): Collection;
}
