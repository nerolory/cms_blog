<?php

namespace App\Repositories;

use App\DTO\PaymentResultData;
use App\Enums\PaymentIntentStatus;
use App\Models\PaymentIntent;
use App\Models\TokenPackage;
use App\Models\User;
use App\Repositories\Contracts\PaymentIntentRepositoryContract;

/**
 * Репозиторий payment intents.
 */
class PaymentIntentRepository implements PaymentIntentRepositoryContract
{
    public function __construct(protected PaymentIntent $intent) {}

    /**
     * {@inheritdoc}
     */
    public function createPending(User $user, TokenPackage $package, PaymentResultData $payment): PaymentIntent
    {
        return $this->intent->newQuery()->create([
            'user_id' => $user->id,
            'token_package_id' => $package->id,
            'gateway' => $payment->gateway,
            'gateway_transaction_id' => $payment->transactionId,
            'reference' => 'token_package:'.$package->id,
            'amount_cents' => $package->price_cents,
            'currency' => $package->currency,
            'status' => PaymentIntentStatus::Pending,
            'metadata' => ['package_name' => $package->name],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function findByGatewayTransaction(string $gateway, string $transactionId): ?PaymentIntent
    {
        return $this->intent->newQuery()
            ->where('gateway', $gateway)
            ->where('gateway_transaction_id', $transactionId)
            ->first();
    }

    /**
     * {@inheritdoc}
     */
    public function markSucceeded(PaymentIntent $intent, int $tokenTransactionId): PaymentIntent
    {
        $intent->update([
            'status' => PaymentIntentStatus::Succeeded,
            'token_transaction_id' => $tokenTransactionId,
        ]);

        return $intent->fresh() ?? $intent;
    }

    /**
     * {@inheritdoc}
     */
    public function markFailed(PaymentIntent $intent, ?string $reason = null): PaymentIntent
    {
        $metadata = $intent->metadata ?? [];
        if ($reason !== null) {
            $metadata['failure_reason'] = $reason;
        }
        $intent->update(['status' => PaymentIntentStatus::Failed, 'metadata' => $metadata]);

        return $intent->fresh() ?? $intent;
    }

    /**
     * {@inheritdoc}
     */
    public function status(PaymentIntent $intent): PaymentIntentStatus
    {
        return $intent->status;
    }
}
