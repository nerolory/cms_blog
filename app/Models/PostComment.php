<?php

namespace App\Models;

use App\Enums\CommentStatus;
use App\Policies\CommentPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Eloquent-модель комментария к посту.
 *
 * @property int $id
 * @property int $post_id
 * @property int $user_id
 * @property int|null $parent_id
 * @property int|null $reply_to_id
 * @property string $body
 * @property CommentStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Post $post
 * @property-read User $user
 * @property-read PostComment|null $parent
 * @property-read PostComment|null $replyTo
 * @property-read Collection<int, PostComment> $replies
 */
#[UsePolicy(CommentPolicy::class)]
class PostComment extends Model
{
    use SoftDeletes;

    protected $fillable = ['post_id', 'user_id', 'parent_id', 'reply_to_id', 'body', 'status'];

    protected $casts = ['status' => CommentStatus::class];

    /**
     * Пост, к которому относится комментарий.
     *
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Автор комментария.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Родительский комментарий в цепочке.
     *
     * @return BelongsTo<PostComment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Комментарий, на который дан ответ.
     *
     * @return BelongsTo<PostComment, $this>
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }

    /**
     * Ответы на комментарий.
     *
     * @return HasMany<PostComment, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at');
    }

    /**
     * Проверяет root.

     *
     * @return bool
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Прямой ответ на корневой комментарий (с отступом).

     *
     * @return bool
     */
    public function isDirectReplyToRoot(): bool
    {
        return $this->parent_id !== null
            && ($this->reply_to_id === null || $this->reply_to_id === $this->parent_id);
    }

    /**
     * Показывать ссылку на комментарий, на который отвечали.

     *
     * @return bool
     */
    public function shouldShowReplyReference(): bool
    {
        return $this->reply_to_id !== null
            && $this->parent_id !== null
            && $this->reply_to_id !== $this->parent_id;
    }
}
