<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Repositories\Contracts\AiSettingsRepositoryContract;
use App\Services\Contracts\AiAnalysisOrderServiceContract;
use App\Support\Ai\AiSettingKey;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

/**
 * Класс ai analysis order service.
 */
class AiAnalysisOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * test can auto analyze only at configured threshold.
     */
    public function test_can_auto_analyze_only_at_configured_threshold(): void
    {
        $service = app(AiAnalysisOrderServiceContract::class);
        $this->assertFalse($service->canAutoAnalyze(999));
        $this->assertTrue($service->canAutoAnalyze(1000));
    }

    /**
     * test cooldown days can be overridden via settings.
     */
    public function test_cooldown_days_can_be_overridden_via_settings(): void
    {
        Setting::query()->create(['key' => AiSettingKey::CooldownDays->value, 'value' => '3']);
        $this->assertSame(3, app(AiSettingsRepositoryContract::class)->getSettings()->cooldownDays);
    }
}
