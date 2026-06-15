<?php

namespace Tests\Feature;

use App\Services\Contracts\MailSettingsServiceContract;
use Illuminate\Support\Collection;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс mail settings.
 */
class MailSettingsTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    /**
     * Подготавливает окружение теста.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    /**
     * test available presets returns configured providers.
     */
    public function test_available_presets_returns_configured_providers(): void
    {
        $presets = app(MailSettingsServiceContract::class)->availablePresets();
        $this->assertInstanceOf(Collection::class, $presets);
        $this->assertTrue($presets->has('mailpit'));
        $this->assertTrue($presets->has('mailbox'));
    }

    /**
     * test seeder applies default mailpit settings.
     */
    public function test_seeder_applies_default_mailpit_settings(): void
    {
        $settings = app(MailSettingsServiceContract::class)->settings();
        $this->assertSame('preset', $settings->mode);
        $this->assertSame('mailpit', $settings->preset);
    }
}
