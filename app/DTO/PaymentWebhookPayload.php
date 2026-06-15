<?php

namespace App\DTO;

use App\Support\TypeCast;

/**
 * DTO payload webhook платёжного провайдера.
 *
 * @property-read string $event
 * @property-read string $transactionId
 * @property-read string $reference
 * @property-read string $gateway
 */
readonly class PaymentWebhookPayload
{
    /**
     * Собирает DTO из JSON webhook.
     *
     * @param  array<string, mixed>  $raw  тело webhook

     * @return self
     */
    public static function fromJson(array $raw): self
    {
        return new self(
            event: TypeCast::string($raw['event'] ?? ''),
            transactionId: TypeCast::string($raw['transaction_id'] ?? ''),
            reference: TypeCast::string($raw['reference'] ?? ''),
            gateway: TypeCast::string($raw['gateway'] ?? 'http'),
        );
    }

    public function __construct(public string $event, public string $transactionId, public string $reference,
        public string $gateway) {}
}
