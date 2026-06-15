<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Eloquent-модель пакета токенов для покупки.
 *
 * @property int $id
 * @property string $name
 * @property int $token_amount
 * @property int $price_cents
 * @property string $currency
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class TokenPackage extends Model
{
    protected $fillable = ['name', 'token_amount', 'price_cents', 'currency', 'is_active', 'sort_order'];

    protected $casts = ['token_amount' => 'integer', 'price_cents' => 'integer', 'is_active' => 'boolean',
        'sort_order' => 'integer'];
}
