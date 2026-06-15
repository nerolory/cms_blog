<?php

namespace App\Models;

use App\Enums\PaymentIntentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Платёжный intent: токены зачисляются только после verified webhook.
 *
 * @property int $id
 * @property int $user_id
 * @property int $token_package_id
 * @property string $gateway
 * @property string $gateway_transaction_id
 * @property string $reference
 * @property int $amount_cents
 * @property string $currency
 * @property PaymentIntentStatus $status
 * @property ?int $token_transaction_id
 * @property ?array<string, mixed> $metadata
 * @property-read User $user
 * @property-read TokenPackage $tokenPackage
 */
class PaymentIntent extends Model
{
    protected $fillable = ['user_id', 'token_package_id', 'gateway', 'gateway_transaction_id', 'reference',
        'amount_cents', 'currency', 'status', 'token_transaction_id', 'metadata'];

    protected $casts = ['amount_cents' => 'integer', 'status' => PaymentIntentStatus::class,
        'metadata' => 'array', 'token_transaction_id' => 'integer'];

    /**
     * Связь с пользователем intent.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с пакетом токенов.
     *
     * @return BelongsTo<TokenPackage, $this>
     */
    public function tokenPackage(): BelongsTo
    {
        return $this->belongsTo(TokenPackage::class);
    }
}
