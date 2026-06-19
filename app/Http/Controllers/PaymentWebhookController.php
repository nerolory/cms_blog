<?php

namespace App\Http\Controllers;

use App\Services\Contracts\PaymentWebhookServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Webhook платёжного провайдера (без CSRF, только HMAC).

 *
 * @property-read PaymentWebhookServiceContract $webhooks
 */
class PaymentWebhookController extends Controller
{
    public function __construct(protected PaymentWebhookServiceContract $webhooks) {}

    /**
     * Принимает подписанный webhook payment.succeeded.
     *
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        $signature = (string) $request->header('X-Payment-Signature', '');
        $payload = $request->getContent();
        $transaction = $this->webhooks->processSignedPayload($payload, $signature);

        return response()->json([
            'processed' => $transaction !== null,
            'transaction_id' => $transaction?->id,
        ]);
    }
}
