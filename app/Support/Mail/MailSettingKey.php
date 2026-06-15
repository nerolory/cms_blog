<?php

namespace App\Support\Mail;

/**
 * Keys for mail and auth settings in the settings table.
 */
final class MailSettingKey
{
    public const MODE = 'mail.mode';

    public const PRESET = 'mail.preset';

    public const HOST = 'mail.host';

    public const PORT = 'mail.port';

    public const SCHEME = 'mail.scheme';

    public const USERNAME = 'mail.username';

    public const PASSWORD = 'mail.password';

    public const FROM_ADDRESS = 'mail.from_address';

    public const FROM_NAME = 'mail.from_name';

    public const REQUIRE_EMAIL_VERIFICATION = 'auth.require_email_verification';
}
