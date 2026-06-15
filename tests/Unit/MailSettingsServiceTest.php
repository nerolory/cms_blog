<?php

namespace Tests\Unit;

use App\DTO\MailSettingsData;
use App\Services\Contracts\MailSettingsServiceContract;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс mail settings service.
 */
class MailSettingsServiceTest extends TestCase
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
     * test resolves mailpit preset to smtp runtime mailer.
     */
    public function test_resolves_mailpit_preset_to_smtp_runtime_mailer(): void
    {
        $service = app(MailSettingsServiceContract::class);
        $service->saveSettings(new MailSettingsData(mode: 'preset', preset: 'mailpit', host: null, port: null,
            scheme: null, username: null, password: null, fromAddress: 'test@example.com', fromName: 'Test App',
            requireEmailVerification: false));
        $service->applyConfiguration();
        $this->assertSame('runtime', config('mail.default'));
        $this->assertSame('smtp', config('mail.mailers.runtime.transport'));
        $this->assertSame('mailpit', config('mail.mailers.runtime.host'));
        $this->assertSame('test@example.com', config('mail.from.address'));
    }

    /**
     * test custom smtp settings are applied.
     */
    public function test_custom_smtp_settings_are_applied(): void
    {
        $service = app(MailSettingsServiceContract::class);
        $service->saveSettings(new MailSettingsData(mode: 'smtp', preset: null, host: 'smtp.example.com', port: 465,
            scheme: 'smtps', username: 'user', password: 'secret', fromAddress: 'noreply@example.com',
            fromName: 'Example', requireEmailVerification: true));
        $service->applyConfiguration();
        $this->assertSame('smtp.example.com', config('mail.mailers.runtime.host'));
        $this->assertSame(465, config('mail.mailers.runtime.port'));
        $this->assertTrue($service->isEmailVerificationRequired());
    }
}
