<?php

namespace App\Services;

use App\DTO\MailSettingsData;
use App\DTO\ResolvedMailerConfig;
use App\Repositories\Contracts\MailSettingsRepositoryContract;
use App\Services\Contracts\MailSettingsServiceContract;
use App\Support\Cache\ApplicationCacheKeys;
use App\Support\TypeCast;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

/**
 * Сервис mail settings.
 *
 * @property-read MailSettingsRepositoryContract $mailSettingsRepository
 */
class MailSettingsService implements MailSettingsServiceContract
{
    private const CACHE_TTL_SECONDS = 3600;

    private ?MailSettingsData $cachedSettings = null;

    private bool $configurationApplied = false;

    public function __construct(protected MailSettingsRepositoryContract $mailSettingsRepository)
    {
        Event::listen(MessageSending::class, function (): void {
            $this->applyConfiguration();
        });
    }

    /**
     * apply configuration.
     */
    public function applyConfiguration(): bool
    {
        if ($this->configurationApplied) {
            return true;
        }
        $settings = $this->settings();
        $resolved = $this->resolveMailer($settings);
        $runtimeMailer = TypeCast::string(config('mail-module.runtime_mailer', 'runtime'), 'runtime');
        Config::set('mail.mailers.'.$runtimeMailer, $resolved->toMailerArray());
        Config::set('mail.default', $runtimeMailer);
        Config::set('mail.from.address', $settings->fromAddress);
        Config::set('mail.from.name', $settings->fromName);
        $this->configurationApplied = true;

        return true;
    }

    /**
     * settings.
     */
    public function settings(): MailSettingsData
    {
        if ($this->cachedSettings instanceof MailSettingsData) {
            return $this->cachedSettings;
        }

        /** @var array<string, mixed> $payload */
        $payload = Cache::remember(ApplicationCacheKeys::MAIL_SETTINGS, self::CACHE_TTL_SECONDS,
            fn (): array => $this->mailSettingsPayloadFromDatabase());

        return $this->cachedSettings = $this->mailSettingsFromPayload($payload);
    }

    /**
     * save settings.
     */
    public function saveSettings(MailSettingsData $data): MailSettingsData
    {
        $this->mailSettingsRepository->saveMailSettings($data);
        $this->forgetSettingsCache();
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
     */
    public function isEmailVerificationRequired(): bool
    {
        return $this->settings()->requireEmailVerification;
    }

    /**
     * Сбрасывает кэш настроек почты.
     */
    public function forgetSettingsCache(): void
    {
        Cache::forget(ApplicationCacheKeys::MAIL_SETTINGS);
        $this->cachedSettings = null;
        $this->configurationApplied = false;
    }

    /**
     * @return array<string, mixed>
     */
    private function mailSettingsPayloadFromDatabase(): array
    {
        $settings = $this->mailSettingsRepository->getMailSettings();

        return [
            'mode' => $settings->mode,
            'preset' => $settings->preset,
            'host' => $settings->host,
            'port' => $settings->port,
            'scheme' => $settings->scheme,
            'username' => $settings->username,
            'password' => $settings->password,
            'fromAddress' => $settings->fromAddress,
            'fromName' => $settings->fromName,
            'requireEmailVerification' => $settings->requireEmailVerification,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mailSettingsFromPayload(array $payload): MailSettingsData
    {
        return new MailSettingsData(
            mode: TypeCast::string($payload['mode'] ?? 'preset', 'preset'),
            preset: TypeCast::nullableString($payload['preset'] ?? null),
            host: TypeCast::nullableString($payload['host'] ?? null),
            port: TypeCast::nullableInt($payload['port'] ?? null),
            scheme: TypeCast::nullableString($payload['scheme'] ?? null),
            username: TypeCast::nullableString($payload['username'] ?? null),
            password: TypeCast::nullableString($payload['password'] ?? null),
            fromAddress: TypeCast::string($payload['fromAddress'] ?? 'noreply@example.com'),
            fromName: TypeCast::string($payload['fromName'] ?? 'Laravel'),
            requireEmailVerification: TypeCast::bool($payload['requireEmailVerification'] ?? false),
        );
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
