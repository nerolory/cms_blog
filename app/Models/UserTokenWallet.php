<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Eloquent-модель кошелька токенов пользователя.
 *
 * @property int $id
 * @property int $user_id
 * @property int $balance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Collection<int, TokenTransaction> $transactions
 */
class UserTokenWallet extends Model
{
    protected $fillable = ['user_id', 'balance'];

    protected $casts = ['balance' => 'integer'];

    /**
     * user.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * transactions.
     *
     * @return HasMany<TokenTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(TokenTransaction::class, 'user_id', 'user_id');
    }
}
