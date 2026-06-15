<?php

namespace App\Services\Payment;

use App\DTO\PaymentChargeData;
use App\DTO\PaymentResultData;
use App\Services\Contracts\PaymentGatewayContract;
use App\Support\TypeCast;
use Illuminate\Support\Facades\Http;

/**
 * HTTP-адаптер: POST /charges к внешнему payment API (production).
 */
class HttpPaymentGateway implements PaymentGatewayContract
{
    /**
     * {@inheritdoc}
     */
    public function charge(PaymentChargeData $charge): PaymentResultData
    {
        $baseUrl = TypeCast::trimRequired(config('payment.http.base_url'));
        if ($baseUrl === '') {
            return new PaymentResultData(successful: false, transactionId: '', gateway: 'http',
                failureReason: __('tokens.errors.gateway_unavailable'));
        }

        try {
            $response = Http::timeout(TypeCast::int(config('payment.http.timeout', 10)))
                ->withToken(TypeCast::string(config('payment.http.secret')))
                ->acceptJson()
                ->post(rtrim($baseUrl, '/').'/charges', [
                    'user_id' => $charge->userId,
                    'amount_cents' => $charge->amountCents,
                    'currency' => $charge->currency,
                    'description' => $charge->description,
                    'reference' => $charge->reference,
                ]);
        } catch (\Throwable $exception) {
            return new PaymentResultData(successful: false, transactionId: '', gateway: 'http',
                failureReason: $exception->getMessage());
        }

        if (! $response->successful()) {
            $reason = TypeCast::nullableString($response->json('failure_reason'))
                ?? TypeCast::nullableString($response->json('error'))
                ?? 'http_'.$response->status();

            return new PaymentResultData(successful: false, transactionId: '', gateway: 'http',
                failureReason: $reason);
        }

        /** @var array<string, mixed> $payload */
        $payload = TypeCast::array($response->json());

        return new PaymentResultData(
            successful: TypeCast::bool($payload['successful'] ?? false),
            transactionId: TypeCast::string($payload['transaction_id'] ?? ''),
            gateway: 'http',
            failureReason: TypeCast::nullableString($payload['failure_reason'] ?? null),
        );
    }
}
