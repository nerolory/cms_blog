<?php

namespace App\Models;

use App\Enums\AiAnalysisOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Eloquent-модель заказа AI-анализа комментариев.
 *
 * @property int $id
 * @property int $user_id
 * @property int $post_id
 * @property int $comment_count
 * @property int $tokens_required
 * @property int|null $tokens_charged
 * @property AiAnalysisOrderStatus $status
 * @property int|null $reviewed_by
 * @property string|null $admin_note
 * @property Carbon|null $reviewed_at
 * @property Carbon|null $executed_at
 * @property Carbon|null $cooldown_until
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Post $post
 * @property-read User|null $reviewer
 */
class AiAnalysisOrder extends Model
{
    protected $fillable = ['user_id', 'post_id', 'comment_count', 'tokens_required', 'tokens_charged', 'status',
        'reviewed_by', 'admin_note', 'reviewed_at', 'executed_at', 'cooldown_until'];

    protected $casts = ['comment_count' => 'integer', 'tokens_required' => 'integer', 'tokens_charged' => 'integer',
        'status' => AiAnalysisOrderStatus::class, 'reviewed_at' => 'datetime', 'executed_at' => 'datetime',
        'cooldown_until' => 'datetime'];

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
     * post.
     *
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * reviewer.
     *
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
