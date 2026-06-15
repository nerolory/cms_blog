<?php

namespace App\Support\Payment;

use App\Support\TypeCast;

/**
 * HMAC-подпись webhook payload (X-Payment-Signature: sha256=...).
 */
final class PaymentWebhookSigner
{
    /**
     * Формирует заголовок подписи для raw JSON body.
     *
     * @return string
     */
    public function sign(string $payload): string
    {
        $secret = TypeCast::trimRequired(config('payment.webhook.secret'));

        return 'sha256='.hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Проверяет подпись webhook.
     *
     * @return bool
     */
    public function verify(string $payload, string $signatureHeader): bool
    {
        $secret = TypeCast::trimRequired(config('payment.webhook.secret'));
        if ($secret === '' || $signatureHeader === '') {
            return false;
        }
        $expected = 'sha256='.hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signatureHeader);
    }
}
