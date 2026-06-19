<?php

namespace App\Services;

use App\DTO\PaymentResultData;
use App\DTO\PaymentWebhookPayload;
use App\Enums\PaymentIntentStatus;
use App\Models\PaymentIntent;
use App\Models\TokenTransaction;
use App\Repositories\Contracts\PaymentIntentRepositoryContract;
use App\Repositories\Contracts\TokenPackageRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Repositories\Contracts\UserTokenWalletRepositoryContract;
use App\Services\Contracts\PaymentWebhookServiceContract;
use App\Support\Payment\PaymentWebhookSigner;
use Illuminate\Support\Facades\DB;

/**
 * Verified webhook: зачисление токенов после purchase.

 *
 * @property-read PaymentWebhookSigner $signer
 * @property-read PaymentIntentRepositoryContract $paymentIntents
 * @property-read UserTokenWalletRepositoryContract $wallets
 * @property-read TokenPackageRepositoryContract $packages
 * @property-read UserRepositoryContract $users
 */
class PaymentWebhookService implements PaymentWebhookServiceContract
{
    public function __construct(protected PaymentWebhookSigner $signer,
        protected PaymentIntentRepositoryContract $paymentIntents,
        protected UserTokenWalletRepositoryContract $wallets,
        protected TokenPackageRepositoryContract $packages, protected UserRepositoryContract $users) {}

    /**
     * {@inheritdoc}

     *
     * @return ?TokenTransaction
     */
    public function processSignedPayload(string $rawPayload, string $signatureHeader): ?TokenTransaction
    {
        if (! $this->signer->verify($rawPayload, $signatureHeader)) {
            abort(401, 'Invalid payment webhook signature.');
        }

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($rawPayload, true, 512, JSON_THROW_ON_ERROR);
        $payload = PaymentWebhookPayload::fromJson($decoded);

        if ($payload->event !== 'payment.succeeded') {
            return null;
        }

        $intent = $this->paymentIntents->findByGatewayTransaction($payload->gateway, $payload->transactionId);
        if ($intent === null) {
            abort(404, 'Payment intent not found.');
        }

        return $this->completeIntent($intent);
    }

    /**
     * {@inheritdoc}

     *
     * @return TokenTransaction
     */
    public function confirmMockPayment(PaymentIntent $intent, PaymentResultData $payment): TokenTransaction
    {
        $rawPayload = json_encode([
            'event' => 'payment.succeeded',
            'transaction_id' => $payment->transactionId,
            'reference' => $intent->reference,
            'gateway' => $payment->gateway,
        ], JSON_THROW_ON_ERROR);
        $signature = $this->signer->sign($rawPayload);
        $transaction = $this->processSignedPayload($rawPayload, $signature);
        if ($transaction === null) {
            throw new \RuntimeException('Mock payment webhook did not produce a transaction.');
        }

        return $transaction;
    }

    private function completeIntent(PaymentIntent $intent): TokenTransaction
    {
        if ($this->paymentIntents->status($intent) === PaymentIntentStatus::Succeeded
            && $intent->token_transaction_id !== null) {
            $existing = TokenTransaction::query()->find($intent->token_transaction_id);

            return $existing ?? throw new \RuntimeException('Token transaction missing for succeeded intent.');
        }

        return DB::transaction(function () use ($intent): TokenTransaction {
            $intent->refresh();
            if ($intent->status === PaymentIntentStatus::Succeeded && $intent->token_transaction_id !== null) {
                return TokenTransaction::query()->findOrFail($intent->token_transaction_id);
            }

            $user = $this->users->findByIdOrFail($intent->user_id);
            $package = $this->packages->findById($intent->token_package_id);
            if ($package === null) {
                $this->paymentIntents->markFailed($intent, 'package_missing');

                throw new \RuntimeException('Token package not found for payment intent.');
            }

            $payment = new PaymentResultData(
                successful: true,
                transactionId: $intent->gateway_transaction_id,
                gateway: $intent->gateway,
            );
            $transaction = $this->wallets->purchase($user, $package, $payment);
            $this->paymentIntents->markSucceeded($intent, $transaction->id);

            return $transaction;
        });
    }
}
