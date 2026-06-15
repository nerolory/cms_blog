<?php

namespace App\Models;

use App\Enums\AiResultStatus;
use App\Enums\AiToolCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Eloquent-модель результата AI-инструмента.
 *
 * @property int $id
 * @property string $subject_type
 * @property int $subject_id
 * @property AiToolCode $tool_code
 * @property AiResultStatus $status
 * @property array<string, mixed>|null $payload
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $completed_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model $subject
 */
class AiToolResult extends Model
{
    protected $fillable = ['subject_type', 'subject_id', 'tool_code', 'status', 'payload', 'metadata', 'completed_at',
        'expires_at'];

    protected $casts = ['tool_code' => AiToolCode::class, 'status' => AiResultStatus::class, 'payload' => 'array',
        'metadata' => 'array', 'completed_at' => 'datetime', 'expires_at' => 'datetime'];

    /**
     * subject.
     *
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
