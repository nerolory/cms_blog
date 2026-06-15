<?php

namespace App\Models;

use App\Enums\TokenTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Eloquent-модель транзакции токенов пользователя.
 *
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property TokenTransactionType $type
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Model|null $reference
 */
class TokenTransaction extends Model
{
    protected $fillable = ['user_id', 'amount', 'type', 'reference_type', 'reference_id', 'metadata'];

    protected $casts = ['amount' => 'integer', 'type' => TokenTransactionType::class, 'metadata' => 'array'];

    /**
     * Владелец транзакции.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * reference.
     *
     * @return MorphTo<Model, $this>
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
