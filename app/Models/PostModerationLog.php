<?php

namespace App\Models;

use App\Enums\PostModerationAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Eloquent-модель записи журнала модерации поста.
 *
 * @property int $id
 * @property int $post_id
 * @property int $actor_id
 * @property PostModerationAction $action
 * @property string|null $reason
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Post $post
 * @property-read User $actor
 */
class PostModerationLog extends Model
{
    protected $fillable = ['post_id', 'actor_id', 'action', 'reason', 'metadata'];

    protected $casts = ['action' => PostModerationAction::class, 'metadata' => 'array'];

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
     * actor.
     *
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
