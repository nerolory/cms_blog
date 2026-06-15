<?php

namespace App\Services\Contracts;

use App\DTO\MailSettingsData;
use Illuminate\Support\Collection;

/**
 * Контракт сервиса mail settings.
 */
interface MailSettingsServiceContract
{
    /**
     * apply configuration.

     *
     * @return bool
     */
    public function applyConfiguration(): bool;

    /**
     * settings.

     *
     * @return MailSettingsData
     */
    public function settings(): MailSettingsData;

    /**
     * save settings.
     *
     * @param  MailSettingsData  $data  данные формы

     * @return MailSettingsData
     */
    public function saveSettings(MailSettingsData $data): MailSettingsData;

    /**
     * available presets.
     */
    /**
     * available presets.
     */
    /**
     * Возвращает доступные пресеты почты.
     *
     * @return Collection<string, string>
     */
    public function availablePresets(): Collection;

    /**
     * send test message.

     *
     * @return bool
     */
    public function sendTestMessage(string $recipient): bool;

    /**
     * Проверяет email verification required.

     *
     * @return bool
     */
    public function isEmailVerificationRequired(): bool;
}
