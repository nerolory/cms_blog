<?php

namespace App\Repositories;

use App\DTO\SearchSettingsData;
use App\Enums\SearchDriver;
use App\Models\Setting;
use App\Repositories\Contracts\SearchSettingsRepositoryContract;
use App\Support\Search\SearchSettingKey;
use App\Support\TypeCast;

/**
 * Репозиторий search settings.

 *
 * @property-read Setting $setting
 */
class SearchSettingsRepository implements SearchSettingsRepositoryContract
{
    public function __construct(protected Setting $setting) {}

    /**
     * Возвращает .
     *
     * @param  string  $key  ключ

     * @return ?string
     */
    public function get(string $key): ?string
    {
        $record = $this->setting->newQuery()->find($key);
        if ($record === null || $record->value === null) {
            return null;
        }

        return $record->value;
    }

    /**
     * set.
     *
     * @param  string  $key  ключ
     */
    public function set(string $key, ?string $value): void
    {
        if ($value === null) {
            $this->setting->newQuery()->where('key', $key)->delete();

            return;
        }
        $this->setting->newQuery()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Возвращает search settings.

     *
     * @return SearchSettingsData
     */
    public function getSearchSettings(): SearchSettingsData
    {
        $stored = $this->get(SearchSettingKey::DRIVER);
        $default = TypeCast::string(config('search.default_driver', SearchDriver::Database->value));

        return new SearchSettingsData(driver: SearchDriver::tryFromString($stored ?? $default));
    }

    /**
     * save search settings.
     *
     * @param  SearchSettingsData  $data  данные формы
     */
    public function saveSearchSettings(SearchSettingsData $data): void
    {
        $this->set(SearchSettingKey::DRIVER, $data->driver->value);
    }
}
