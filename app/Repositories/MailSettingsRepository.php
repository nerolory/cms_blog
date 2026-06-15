<?php

namespace App\Repositories;

use App\DTO\MailSettingsData;
use App\Models\Setting;
use App\Repositories\Contracts\MailSettingsRepositoryContract;
use App\Support\Mail\MailSettingKey;
use App\Support\TypeCast;
use Illuminate\Support\Facades\Crypt;

/**
 * Репозиторий mail settings.

 *
 * @property-read Setting $setting
 */
class MailSettingsRepository implements MailSettingsRepositoryContract
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
        if ($this->isEncryptedKey($key)) {
            return Crypt::decryptString($record->value);
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
        $storedValue = $this->isEncryptedKey($key) ? Crypt::encryptString($value) : $value;
        $this->setting->newQuery()->updateOrCreate(['key' => $key], ['value' => $storedValue]);
    }

    /**
     * Возвращает mail settings.

     *
     * @return MailSettingsData
     */
    public function getMailSettings(): MailSettingsData
    {
        /** @var array<string, mixed> $defaults */
        $defaults = TypeCast::array(config('mail-module.defaults'));

        return new MailSettingsData(
            mode: $this->get(MailSettingKey::MODE) ?? TypeCast::string($defaults['mode'] ?? 'preset', 'preset'),
            preset: $this->get(MailSettingKey::PRESET) ?? TypeCast::string($defaults['preset'] ?? 'mailpit', 'mailpit'),
            host: $this->get(MailSettingKey::HOST) ?? TypeCast::nullableString($defaults['host'] ?? null),
            port: $this->readPort($defaults),
            scheme: $this->get(MailSettingKey::SCHEME) ?? TypeCast::nullableString($defaults['scheme'] ?? null),
            username: $this->get(MailSettingKey::USERNAME) ?? TypeCast::nullableString($defaults['username'] ?? null),
            password: $this->get(MailSettingKey::PASSWORD) ?? TypeCast::nullableString($defaults['password'] ?? null),
            fromAddress: $this->get(MailSettingKey::FROM_ADDRESS)
                ?? TypeCast::string($defaults['from_address'] ?? 'noreply@example.com'),
            fromName: $this->get(MailSettingKey::FROM_NAME)
                ?? TypeCast::string($defaults['from_name'] ?? 'Laravel'),
            requireEmailVerification: $this->readBoolean(
                MailSettingKey::REQUIRE_EMAIL_VERIFICATION,
                TypeCast::bool(config('mail-module.require_email_verification_default', false)),
            ),
        );
    }

    /**
     * save mail settings.
     *
     * @param  MailSettingsData  $data  данные формы
     */
    public function saveMailSettings(MailSettingsData $data): void
    {
        $this->set(MailSettingKey::MODE, $data->mode);
        $this->set(MailSettingKey::PRESET, $data->preset);
        $this->set(MailSettingKey::HOST, $data->host);
        $this->set(MailSettingKey::PORT, $data->port !== null ? (string) $data->port : null);
        $this->set(MailSettingKey::SCHEME, $data->scheme);
        $this->set(MailSettingKey::USERNAME, $data->username);
        if ($data->password !== null && $data->password !== '') {
            $this->set(MailSettingKey::PASSWORD, $data->password);
        }
        $this->set(MailSettingKey::FROM_ADDRESS, $data->fromAddress);
        $this->set(MailSettingKey::FROM_NAME, $data->fromName);
        $this->set(MailSettingKey::REQUIRE_EMAIL_VERIFICATION, $data->requireEmailVerification ? '1' : '0');
    }

    private function isEncryptedKey(string $key): bool
    {
        /** @var list<string> $encryptedKeys */
        $encryptedKeys = config('mail-module.encrypted_keys', []);

        return is_array($encryptedKeys) && in_array($key, $encryptedKeys, true);
    }

    /**
     * @param  array<string, mixed>  $defaults
     */
    private function readPort(array $defaults): ?int
    {
        $stored = $this->get(MailSettingKey::PORT);
        if ($stored !== null && $stored !== '') {
            return TypeCast::int($stored);
        }
        $defaultPort = $defaults['port'] ?? null;

        return $defaultPort !== null && $defaultPort !== '' ? TypeCast::nullableInt($defaultPort) : null;
    }

    private function readBoolean(string $key, bool $default): bool
    {
        $value = $this->get($key);
        if ($value === null) {
            return $default;
        }

        return in_array($value, ['1', 'true', 'yes'], true);
    }
}
