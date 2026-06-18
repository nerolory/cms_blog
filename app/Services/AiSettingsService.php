<?php

namespace App\Services;

use App\DTO\AiSettingsData;
use App\Repositories\Contracts\AiSettingsRepositoryContract;
use App\Services\Contracts\AiSettingsServiceContract;
use App\Support\Cache\ApplicationCacheKeys;
use App\Support\TypeCast;
use Illuminate\Support\Facades\Cache;

/**
 * Сервис настроек AI с межзапросным кэшем.
 *
 * @property-read AiSettingsRepositoryContract $aiSettingsRepository
 */
class AiSettingsService implements AiSettingsServiceContract
{
    private const CACHE_TTL_SECONDS = 3600;

    private ?AiSettingsData $cachedSettings = null;

    public function __construct(protected AiSettingsRepositoryContract $aiSettingsRepository) {}

    public function settings(): AiSettingsData
    {
        if ($this->cachedSettings instanceof AiSettingsData) {
            return $this->cachedSettings;
        }

        /** @var array<string, int> $payload */
        $payload = Cache::remember(
            ApplicationCacheKeys::AI_SETTINGS,
            self::CACHE_TTL_SECONDS,
            fn (): array => $this->settingsPayloadFromDatabase(),
        );

        return $this->cachedSettings = $this->settingsFromPayload($payload);
    }

    public function forgetSettingsCache(): void
    {
        Cache::forget(ApplicationCacheKeys::AI_SETTINGS);
        $this->cachedSettings = null;
    }

    /**
     * @return array<string, int>
     */
    private function settingsPayloadFromDatabase(): array
    {
        $settings = $this->aiSettingsRepository->getSettings();

        return [
            'cooldownDays' => $settings->cooldownDays,
            'autoAnalysisCommentThreshold' => $settings->autoAnalysisCommentThreshold,
            'tokensMinimum' => $settings->tokensMinimum,
            'tokensPerComment' => $settings->tokensPerComment,
        ];
    }

    /**
     * @param  array<string, int>  $payload
     */
    private function settingsFromPayload(array $payload): AiSettingsData
    {
        return new AiSettingsData(
            cooldownDays: TypeCast::int($payload['cooldownDays'] ?? 7, 7),
            autoAnalysisCommentThreshold: TypeCast::int($payload['autoAnalysisCommentThreshold'] ?? 1000, 1000),
            tokensMinimum: TypeCast::int($payload['tokensMinimum'] ?? 10, 10),
            tokensPerComment: TypeCast::int($payload['tokensPerComment'] ?? 1, 1),
        );
    }
}
