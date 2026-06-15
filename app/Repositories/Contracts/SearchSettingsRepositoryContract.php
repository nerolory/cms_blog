<?php

namespace App\Repositories\Contracts;

use App\DTO\SearchSettingsData;

/**
 * Контракт репозитория search settings.
 */
interface SearchSettingsRepositoryContract
{
    /**
     * Возвращает .
     *
     * @param  string  $key  ключ

     * @return ?string
     */
    public function get(string $key): ?string;

    /**
     * set.
     *
     * @param  string  $key  ключ
     */
    public function set(string $key, ?string $value): void;

    /**
     * Возвращает search settings.

     *
     * @return SearchSettingsData
     */
    public function getSearchSettings(): SearchSettingsData;

    /**
     * save search settings.
     *
     * @param  SearchSettingsData  $data  данные формы
     */
    public function saveSearchSettings(SearchSettingsData $data): void;
}
