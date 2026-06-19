<?php

namespace App\Models;

use App\Enums\ReactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Реакция пользователя на комментарий.
 *
 * @property int $id
 * @property int $comment_id
 * @property int $user_id
 * @property ReactionType $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read PostComment $comment
 * @property-read User $user
 */
class CommentReaction extends Model
{
    protected $fillable = ['comment_id', 'user_id', 'type'];

    protected $casts = ['type' => ReactionType::class];

    /**
     * comment.
     *
     * @return BelongsTo<PostComment, $this>
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(PostComment::class, 'comment_id');
    }

    /**
     * user.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
