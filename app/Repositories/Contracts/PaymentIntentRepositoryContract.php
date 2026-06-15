<?php

namespace App\Repositories\Contracts;

use App\DTO\PaymentResultData;
use App\Enums\PaymentIntentStatus;
use App\Models\PaymentIntent;
use App\Models\TokenPackage;
use App\Models\User;

/**
 * Контракт репозитория payment intents.
 */
interface PaymentIntentRepositoryContract
{
    /**
     * Создаёт pending intent после initiate charge.
     *
     * @return PaymentIntent
     */
    public function createPending(User $user, TokenPackage $package, PaymentResultData $payment): PaymentIntent;

    /**
     * Находит intent по gateway transaction id.
     *
     * @return ?PaymentIntent
     */
    public function findByGatewayTransaction(string $gateway, string $transactionId): ?PaymentIntent;

    /**
     * Помечает intent успешным и связывает с token transaction.
     *
     * @return PaymentIntent
     */
    public function markSucceeded(PaymentIntent $intent, int $tokenTransactionId): PaymentIntent;

    /**
     * Помечает intent неуспешным.
     *
     * @return PaymentIntent
     */
    public function markFailed(PaymentIntent $intent, ?string $reason = null): PaymentIntent;

    /**
     * Возвращает статус intent.
     *
     * @return PaymentIntentStatus
     */
    public function status(PaymentIntent $intent): PaymentIntentStatus;
}
