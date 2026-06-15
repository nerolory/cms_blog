<?php

namespace App\Support\Config;

use App\Support\TypeCast;
use RuntimeException;

/**
 * Обязательные настройки production: health allowlist и пароль owner при seed.
 */
final class ProductionConfigGuard
{
    private const DEFAULT_OWNER_PASSWORD = 'password';

    /**
     * Проверяет конфиг при boot в production (HEALTH_ALLOWED_IPS).
     */
    public static function assertBootRequirements(): void
    {
        if (! app()->environment('production')) {
            return;
        }

        self::assertHealthAllowlistConfigured();
        self::assertPaymentWebhookConfigured();
    }

    /**
     * Запрещает seed/bootstrap owner с дефолтным или пустым паролем в production.
     */
    public static function assertOwnerPasswordSafe(): void
    {
        if (! app()->environment('production')) {
            return;
        }

        $password = TypeCast::trimRequired(config('seeding.owner.password'));
        if ($password === '' || $password === self::DEFAULT_OWNER_PASSWORD) {
            throw new RuntimeException(
                'SEED_OWNER_PASSWORD must be set to a strong non-default value in production.',
            );
        }
    }

    /**
     * @throws RuntimeException
     */
    private static function assertPaymentWebhookConfigured(): void
    {
        $gateway = TypeCast::trimRequired(config('payment.gateway', ''));
        if ($gateway !== 'http') {
            return;
        }

        $secret = TypeCast::trimRequired(config('payment.webhook.secret', ''));
        if ($secret === '') {
            throw new RuntimeException('PAYMENT_WEBHOOK_SECRET must be configured when PAYMENT_GATEWAY=http.');
        }
    }

    /**
     * @throws RuntimeException
     */
    private static function assertHealthAllowlistConfigured(): void
    {
        /** @var list<string> $allowedIps */
        $allowedIps = TypeCast::array(config('health.allowed_ips', []));
        if ($allowedIps === []) {
            throw new RuntimeException('HEALTH_ALLOWED_IPS must be configured in production (.env).');
        }
    }
}
