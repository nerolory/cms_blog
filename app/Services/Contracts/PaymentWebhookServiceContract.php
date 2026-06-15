<?php

namespace App\Services\Contracts;

use App\DTO\PaymentResultData;
use App\Models\PaymentIntent;
use App\Models\TokenTransaction;

/**
 * Контракт обработки verified payment webhooks.
 */
interface PaymentWebhookServiceContract
{
    /**
     * Обрабатывает подписанный webhook и зачисляет токены при success.
     *
     * @return ?TokenTransaction
     */
    public function processSignedPayload(string $rawPayload, string $signatureHeader): ?TokenTransaction;

    /**
     * Подтверждает mock-платёж через тот же verified webhook pipeline.
     *
     * @return TokenTransaction
     */
    public function confirmMockPayment(PaymentIntent $intent, PaymentResultData $payment): TokenTransaction;
}
