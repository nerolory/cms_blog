<?php

namespace App\Services;

use App\DTO\SiteSettingsData;
use App\Repositories\Contracts\SiteSettingsRepositoryContract;
use App\Services\Contracts\SiteSettingsServiceContract;
use App\Support\Cache\ApplicationCacheKeys;
use App\Support\TypeCast;
use Illuminate\Support\Facades\Cache;

/**
 * Сервис настроек брендинга сайта с долгим кэшем.
 *
 * @property-read SiteSettingsRepositoryContract $siteSettingsRepository
 */
class SiteSettingsService implements SiteSettingsServiceContract
{
    private const CACHE_TTL_SECONDS = 31_536_000;

    private ?SiteSettingsData $cachedSettings = null;

    public function __construct(protected SiteSettingsRepositoryContract $siteSettingsRepository) {}

    /**
     * {@inheritDoc}
     */
    public function siteName(): string
    {
        return $this->settings()->siteName;
    }

    /**
     * {@inheritDoc}
     */
    public function settings(): SiteSettingsData
    {
        if ($this->cachedSettings instanceof SiteSettingsData) {
            return $this->cachedSettings;
        }

        /** @var array{site_name: string} $payload */
        $payload = Cache::remember(
            ApplicationCacheKeys::SITE_BRANDING,
            self::CACHE_TTL_SECONDS,
            fn (): array => $this->brandingPayloadFromDatabase(),
        );

        return $this->cachedSettings = $this->settingsFromPayload($payload);
    }

    /**
     * {@inheritDoc}
     */
    public function saveSettings(SiteSettingsData $data): SiteSettingsData
    {
        $this->siteSettingsRepository->saveSiteSettings($data);
        $this->forgetSettingsCache();

        return $this->settings();
    }

    /**
     * @return array{site_name: string}
     */
    private function brandingPayloadFromDatabase(): array
    {
        $settings = $this->siteSettingsRepository->getSiteSettings();

        return ['site_name' => $settings->siteName];
    }

    /**
     * @param  array{site_name: string}  $payload
     */
    private function settingsFromPayload(array $payload): SiteSettingsData
    {
        return new SiteSettingsData(
            siteName: TypeCast::string($payload['site_name'] ?? config('app.name', 'Laravel')),
        );
    }

    private function forgetSettingsCache(): void
    {
        Cache::forget(ApplicationCacheKeys::SITE_BRANDING);
        $this->cachedSettings = null;
    }
}
