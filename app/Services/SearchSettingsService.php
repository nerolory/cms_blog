<?php

namespace App\Services;

use App\DTO\SearchSettingsData;
use App\Enums\SearchDriver;
use App\Repositories\Contracts\SearchSettingsRepositoryContract;
use App\Services\Contracts\SearchSettingsServiceContract;
use Illuminate\Support\Collection;

/**
 * Сервис search settings.
 *
 * @property-read SearchSettingsRepositoryContract $searchSettingsRepository
 */
class SearchSettingsService implements SearchSettingsServiceContract
{
    public function __construct(protected SearchSettingsRepositoryContract $searchSettingsRepository) {}

    /**
     * settings.

     *
     * @return SearchSettingsData
     */
    public function settings(): SearchSettingsData
    {
        return $this->searchSettingsRepository->getSearchSettings();
    }

    /**
     * save settings.
     *
     * @param  SearchSettingsData  $data  данные формы

     * @return SearchSettingsData
     */
    public function saveSettings(SearchSettingsData $data): SearchSettingsData
    {
        $this->searchSettingsRepository->saveSearchSettings($data);

        return $this->settings();
    }

    /**
     * available drivers.
     */ /**
     * Возвращает доступные драйверы поиска.
     *
     * @return Collection<string, string>
     */
    public function availableDrivers(): Collection
    {
        return collect(SearchDriver::cases())->mapWithKeys(fn (SearchDriver $driver): array => [$driver
            ->value => $driver->label()]);
    }
}
