<?php

namespace App\Services;

use App\DTO\MailSettingsData;
use App\DTO\ResolvedMailerConfig;
use App\Repositories\Contracts\MailSettingsRepositoryContract;
use App\Services\Contracts\MailSettingsServiceContract;
use App\Support\TypeCast;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

/**
 * Сервис mail settings.
 *
 * @property-read MailSettingsRepositoryContract $mailSettingsRepository
 */
class MailSettingsService implements MailSettingsServiceContract
{
    public function __construct(protected MailSettingsRepositoryContract $mailSettingsRepository) {}

    /**
     * apply configuration.

     *
     * @return bool
     */
    public function applyConfiguration(): bool
    {
        $settings = $this->settings();
        $resolved = $this->resolveMailer($settings);
        $runtimeMailer = TypeCast::string(config('mail-module.runtime_mailer', 'runtime'), 'runtime');
        Config::set('mail.mailers.'.$runtimeMailer, $resolved->toMailerArray());
        Config::set('mail.default', $runtimeMailer);
        Config::set('mail.from.address', $settings->fromAddress);
        Config::set('mail.from.name', $settings->fromName);

        return true;
    }

    /**
     * settings.

     *
     * @return MailSettingsData
     */
    public function settings(): MailSettingsData
    {
        return $this->mailSettingsRepository->getMailSettings();
    }

    /**
     * save settings.
     *
     * @param  MailSettingsData  $data  данные формы

     * @return MailSettingsData
     */
    public function saveSettings(MailSettingsData $data): MailSettingsData
    {
        $this->mailSettingsRepository->saveMailSettings($data);
        $this->applyConfiguration();

        return $this->settings();
    }

    /**
     * available presets.
     */
    /**
     * Возвращает доступные пресеты почты.
     *
     * @return Collection<string, string>
     */
    public function availablePresets(): Collection
    {
        /** @var array<string, array{label: string}> $presets */
        $presets = config('mail-presets', []);

        return collect($presets)->mapWithKeys(fn (array $preset,
            string $key): array => [$key => TypeCast::string($preset['label'] ?? $key, $key)]);
    }

    /**
     * send test message.

     *
     * @return bool
     */
    public function sendTestMessage(string $recipient): bool
    {
        $this->applyConfiguration();
        Mail::raw('Mail ping from '.TypeCast::string(config('app.name')).' at '.now()->toDateTimeString(),
            static function ($message) use ($recipient): void {
                $message->to($recipient)->subject('Mail ping');
            });

        return true;
    }

    /**
     * Проверяет email verification required.

     *
     * @return bool
     */
    public function isEmailVerificationRequired(): bool
    {
        return $this->settings()->requireEmailVerification;
    }

    private function resolveMailer(MailSettingsData $settings): ResolvedMailerConfig
    {
        $runtimeMailer = TypeCast::string(config('mail-module.runtime_mailer', 'runtime'), 'runtime');
        if ($settings->mode === 'smtp') {
            return new ResolvedMailerConfig(mailerName: $runtimeMailer, transport: 'smtp', host: $settings->host,
                port: $settings->port, scheme: $settings->scheme, username: $settings->username,
                password: $settings->password, path: null, localDomain: $this->localDomain());
        }
        $presetKey = $settings->preset ?? 'mailpit';
        /** @var array<string, mixed>|null $preset */
        $preset = config('mail-presets.'.$presetKey);
        if ($preset === null) {
            throw new InvalidArgumentException("Unknown mail preset [{$presetKey}].");
        }
        /** @var array<string, mixed> $mailer */
        $mailer = TypeCast::array($preset['mailer'] ?? []);
        $transport = TypeCast::string($mailer['transport'] ?? 'smtp', 'smtp');
        if ($transport === 'sendmail') {
            return new ResolvedMailerConfig(mailerName: $runtimeMailer, transport: 'sendmail', host: null, port: null,
                scheme: null, username: null, password: null, path: TypeCast::nullableString($mailer['path'] ?? null),
                localDomain: null);
        }
        $requiresAuth = TypeCast::bool($preset['requires_auth'] ?? false);

        return new ResolvedMailerConfig(mailerName: $runtimeMailer, transport: 'smtp',
            host: TypeCast::nullableString($mailer['host'] ?? null),
            port: TypeCast::nullableInt($mailer['port'] ?? null),
            scheme: TypeCast::nullableString($mailer['scheme'] ?? null),
            username: $requiresAuth ? $settings->username : TypeCast::nullableString($mailer['username'] ?? null),
            password: $requiresAuth ? $settings->password : TypeCast::nullableString($mailer['password'] ?? null),
            path: null, localDomain: TypeCast::nullableString($mailer['local_domain'] ?? null) ?? $this->localDomain());
    }

    private function localDomain(): ?string
    {
        $host = parse_url(TypeCast::string(config('app.url', 'http://localhost')), PHP_URL_HOST);

        return is_string($host) ? $host : null;
    }
}
