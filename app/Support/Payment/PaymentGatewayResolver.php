<?php

namespace App\Support\Payment;

use App\Services\Payment\DisabledPaymentGateway;
use App\Services\Payment\HttpPaymentGateway;
use App\Services\Payment\MockPaymentGateway;

/**
 * Выбор реализации payment gateway по config и окружению.
 *
 * Не бросает исключений при boot: оплата — опциональный модуль.
 */
final class PaymentGatewayResolver
{
    /**
     * Возвращает FQCN реализации gateway.
     *
     * @return class-string
     */
    public static function resolveImplementation(string $gateway, string $environment): string
    {
        if ($gateway === '' || self::isDisabledGateway($gateway)) {
            return DisabledPaymentGateway::class;
        }

        if ($environment === 'production' && $gateway === 'mock') {
            return DisabledPaymentGateway::class;
        }

        return match ($gateway) {
            'mock' => MockPaymentGateway::class,
            'http' => HttpPaymentGateway::class,
            default => DisabledPaymentGateway::class,
        };
    }

    private static function isDisabledGateway(string $gateway): bool
    {
        return in_array($gateway, ['none', 'disabled', 'off'], true);
    }

    /**
     * Нормализует имя gateway для сравнения.
     *
     * @return string
     */
    public static function normalizeGatewayName(string $gateway): string
    {
        return strtolower(trim($gateway));
    }
}
