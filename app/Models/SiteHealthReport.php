<?php

namespace App\Models;

use App\Enums\SiteHealthContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Eloquent-модель отчёта о состоянии сайта.
 *
 * @property int $id
 * @property SiteHealthContext $context
 * @property int $critical_count
 * @property int $warning_count
 * @property int $passed_count
 * @property array<int, array<string, mixed>>|null $results
 * @property int|null $triggered_by
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $triggeredBy
 */
class SiteHealthReport extends Model
{
    protected $fillable = ['context', 'critical_count', 'warning_count', 'passed_count', 'results', 'triggered_by',
        'completed_at'];

    protected $casts = ['context' => SiteHealthContext::class, 'results' => 'array', 'critical_count' => 'integer',
        'warning_count' => 'integer', 'passed_count' => 'integer', 'completed_at' => 'datetime'];

    /**
     * triggered by.
     *
     * @return BelongsTo<User, $this>
     */
    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
