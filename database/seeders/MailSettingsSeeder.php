<?php

namespace Database\Seeders;

use App\Repositories\Contracts\MailSettingsRepositoryContract;
use App\Support\Mail\MailSettingKey;
use App\Support\TypeCast;
use Illuminate\Database\Seeder;

/**
 * Default mail settings (Mailpit) for development.
 */
class MailSettingsSeeder extends Seeder
{
    /**
     * run.
     */
    public function run(): void
    {
        $repository = app(MailSettingsRepositoryContract::class);
        if ($repository->get(MailSettingKey::MODE) !== null) {
            return;
        }
        /** @var array<string, mixed> $defaults */
        $defaults = TypeCast::array(config('mail-module.defaults'));
        $repository->set(MailSettingKey::MODE, TypeCast::string($defaults['mode'] ?? 'preset', 'preset'));
        $repository->set(MailSettingKey::PRESET, TypeCast::string($defaults['preset'] ?? 'mailpit', 'mailpit'));
        $repository->set(MailSettingKey::FROM_ADDRESS,
            TypeCast::string($defaults['from_address'] ?? 'noreply@example.com'));
        $repository->set(MailSettingKey::FROM_NAME, TypeCast::string($defaults['from_name'] ?? 'Laravel'));
        $repository->set(MailSettingKey::REQUIRE_EMAIL_VERIFICATION,
            TypeCast::bool(config('mail-module.require_email_verification_default')) ? '1' : '0');
    }
}
