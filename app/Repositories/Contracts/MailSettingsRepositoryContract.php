<?php

namespace App\Repositories\Contracts;

use App\DTO\MailSettingsData;

/**
 * Контракт репозитория mail settings.
 */
interface MailSettingsRepositoryContract
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
     * Возвращает mail settings.

     *
     * @return MailSettingsData
     */
    public function getMailSettings(): MailSettingsData;

    /**
     * save mail settings.
     *
     * @param  MailSettingsData  $data  данные формы
     */
    public function saveMailSettings(MailSettingsData $data): void;
}
