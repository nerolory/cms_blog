<?php

namespace App\Repositories;

use App\DTO\AiSettingsData;
use App\Models\Setting;
use App\Repositories\Contracts\AiSettingsRepositoryContract;
use App\Services\Contracts\AiSettingsServiceContract;
use App\Support\Ai\AiSettingKey;
use App\Support\TypeCast;

/**
 * Репозиторий ai settings.

 *
 * @property-read Setting $setting
 */
class AiSettingsRepository implements AiSettingsRepositoryContract
{
    public function __construct(protected Setting $setting) {}

    /**
     * Возвращает .
     *
     * @param  AiSettingKey  $key  ключ

     * @return ?string
     */
    public function get(AiSettingKey $key): ?string
    {
        $record = $this->setting->newQuery()->find($key->value);

        return $record?->value;
    }

    /**
     * set.
     *
     * @param  AiSettingKey  $key  ключ
     */
    public function set(AiSettingKey $key, ?string $value): void
    {
        if ($value === null) {
            $this->setting->newQuery()->where('key', $key->value)->delete();

            return;
        }
        $this->setting->newQuery()->updateOrCreate(['key' => $key->value], ['value' => $value]);
        if (app()->bound(AiSettingsServiceContract::class)) {
            app(AiSettingsServiceContract::class)->forgetSettingsCache();
        }
    }

    /**
     * Возвращает settings.

     *
     * @return AiSettingsData
     */
    public function getSettings(): AiSettingsData
    {
        /** @var array<string, mixed> $defaults */
        $defaults = TypeCast::array(config('ai', []));
        /** @var array<string, mixed> $tokenDefaults */
        $tokenDefaults = TypeCast::array($defaults['tokens'] ?? []);

        $values = $this->getMany([
            AiSettingKey::CooldownDays,
            AiSettingKey::AutoAnalysisThreshold,
            AiSettingKey::TokensMinimum,
            AiSettingKey::TokensPerComment,
        ]);

        return new AiSettingsData(
            cooldownDays: $this->readInt(
                $values[AiSettingKey::CooldownDays->value] ?? null,
                TypeCast::int($defaults['cooldown_days'] ?? 7, 7),
            ),
            autoAnalysisCommentThreshold: $this->readInt(
                $values[AiSettingKey::AutoAnalysisThreshold->value] ?? null,
                TypeCast::int($defaults['auto_analysis_comment_threshold'] ?? 1000, 1000),
            ),
            tokensMinimum: $this->readInt(
                $values[AiSettingKey::TokensMinimum->value] ?? null,
                TypeCast::int($tokenDefaults['minimum'] ?? 10, 10),
            ),
            tokensPerComment: $this->readInt(
                $values[AiSettingKey::TokensPerComment->value] ?? null,
                TypeCast::int($tokenDefaults['per_comment'] ?? 1, 1),
            ),
        );
    }

    /**
     * @param  list<AiSettingKey>  $keys
     * @return array<string, ?string>
     */
    private function getMany(array $keys): array
    {
        $keyValues = array_map(static fn (AiSettingKey $key): string => $key->value, $keys);
        $records = $this->setting->newQuery()->whereIn('key', $keyValues)->pluck('value', 'key');
        $values = [];
        foreach ($keys as $key) {
            $raw = $records->get($key->value);
            $values[$key->value] = is_string($raw) || $raw === null ? $raw : TypeCast::nullableString($raw);
        }

        return $values;
    }

    private function readInt(?string $value, int $default): int
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return max(0, (int) $value);
    }
}
