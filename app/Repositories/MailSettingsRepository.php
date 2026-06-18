<?php

namespace App\Repositories;

use App\DTO\MailSettingsData;
use App\Models\Setting;
use App\Repositories\Contracts\MailSettingsRepositoryContract;
use App\Support\Mail\MailSettingKey;
use App\Support\TypeCast;
use App\Support\Database\SchemaInspector;
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
        return $this->getMany([$key])[$key] ?? null;
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
        if (! SchemaInspector::hasSettingsTable()) {
            return $this->defaultMailSettings();
        }

        /** @var array<string, mixed> $defaults */
        $defaults = TypeCast::array(config('mail-module.defaults'));
        $values = $this->getMany([
            MailSettingKey::MODE,
            MailSettingKey::PRESET,
            MailSettingKey::HOST,
            MailSettingKey::PORT,
            MailSettingKey::SCHEME,
            MailSettingKey::USERNAME,
            MailSettingKey::PASSWORD,
            MailSettingKey::FROM_ADDRESS,
            MailSettingKey::FROM_NAME,
            MailSettingKey::REQUIRE_EMAIL_VERIFICATION,
        ]);

        return new MailSettingsData(
            mode: $values[MailSettingKey::MODE] ?? TypeCast::string($defaults['mode'] ?? 'preset', 'preset'),
            preset: $values[MailSettingKey::PRESET] ?? TypeCast::string($defaults['preset'] ?? 'mailpit', 'mailpit'),
            host: $values[MailSettingKey::HOST] ?? TypeCast::nullableString($defaults['host'] ?? null),
            port: $this->readPortFromValues($values, $defaults),
            scheme: $values[MailSettingKey::SCHEME] ?? TypeCast::nullableString($defaults['scheme'] ?? null),
            username: $values[MailSettingKey::USERNAME] ?? TypeCast::nullableString($defaults['username'] ?? null),
            password: $values[MailSettingKey::PASSWORD] ?? TypeCast::nullableString($defaults['password'] ?? null),
            fromAddress: $values[MailSettingKey::FROM_ADDRESS]
                ?? TypeCast::string($defaults['from_address'] ?? 'noreply@example.com'),
            fromName: $values[MailSettingKey::FROM_NAME]
                ?? TypeCast::string($defaults['from_name'] ?? 'Laravel'),
            requireEmailVerification: $this->readBooleanFromValue(
                $values[MailSettingKey::REQUIRE_EMAIL_VERIFICATION] ?? null,
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
     * @param  list<string>  $keys
     * @return array<string, ?string>
     */
    private function getMany(array $keys): array
    {
        if ($keys === [] || ! SchemaInspector::hasSettingsTable()) {
            return array_fill_keys($keys, null);
        }

        $records = $this->setting->newQuery()->whereIn('key', $keys)->get(['key', 'value']);
        $values = array_fill_keys($keys, null);
        foreach ($records as $record) {
            $key = TypeCast::string($record->key);
            if ($record->value === null) {
                continue;
            }
            $values[$key] = $this->isEncryptedKey($key)
                ? Crypt::decryptString($record->value)
                : $record->value;
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $defaults
     */
    private function readPortFromValues(array $values, array $defaults): ?int
    {
        $stored = $values[MailSettingKey::PORT] ?? null;
        if ($stored !== null && $stored !== '') {
            return TypeCast::int($stored);
        }
        $defaultPort = $defaults['port'] ?? null;

        return $defaultPort !== null && $defaultPort !== '' ? TypeCast::nullableInt($defaultPort) : null;
    }

    private function readBooleanFromValue(?string $value, bool $default): bool
    {
        if ($value === null) {
            return $default;
        }

        return in_array($value, ['1', 'true', 'yes'], true);
    }

    private function defaultMailSettings(): MailSettingsData
    {
        /** @var array<string, mixed> $defaults */
        $defaults = TypeCast::array(config('mail-module.defaults'));

        return new MailSettingsData(
            mode: TypeCast::string($defaults['mode'] ?? 'preset', 'preset'),
            preset: TypeCast::string($defaults['preset'] ?? 'mailpit', 'mailpit'),
            host: TypeCast::nullableString($defaults['host'] ?? null),
            port: TypeCast::nullableInt($defaults['port'] ?? null),
            scheme: TypeCast::nullableString($defaults['scheme'] ?? null),
            username: TypeCast::nullableString($defaults['username'] ?? null),
            password: TypeCast::nullableString($defaults['password'] ?? null),
            fromAddress: TypeCast::string($defaults['from_address'] ?? 'noreply@example.com'),
            fromName: TypeCast::string($defaults['from_name'] ?? 'Laravel'),
            requireEmailVerification: TypeCast::bool(config('mail-module.require_email_verification_default', false)),
        );
    }
}
