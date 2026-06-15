<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Платёж инициирован; зачисление токенов ожидает verified webhook.
 */
class PaymentAwaitingWebhookException extends RuntimeException
{
    /**
     * Создаёт исключение для HTTP gateway без синхронного credit.
     *
     * @return self
     */
    public static function forIntent(): self
    {
        return new self(__('tokens.errors.payment_awaiting_webhook'));
    }
}
