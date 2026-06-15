<?php

use App\Services\Contracts\MailSettingsServiceContract;
use App\Support\TypeCast;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:ping {email?}', function (?string $email, MailSettingsServiceContract $mailSettingsService) {
    $recipient = $email ?? TypeCast::string(config('mail.from.address'));
    $mailSettingsService->sendTestMessage($recipient);

    $mailer = TypeCast::string(config('mail.default'));

    $this->info("Sent test message to {$recipient} via mailer [{$mailer}].");

    if ($mailer === 'runtime' || $mailer === 'mailpit') {
        $uiPort = TypeCast::int(config('mail.mailpit_ui_port'));
        $this->line("Mailpit UI: http://localhost:{$uiPort}");
    }
})->purpose('Send a test email using the configured mailer');
