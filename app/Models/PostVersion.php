<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Eloquent-модель снимка версии поста.
 *
 * @property int $id
 * @property int $post_id
 * @property int|null $created_by
 * @property int $version_number
 * @property array<string, mixed>|null $snapshot
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Post $post
 * @property-read User|null $author
 */
class PostVersion extends Model
{
    protected $fillable = ['post_id', 'created_by', 'version_number', 'snapshot'];

    protected $casts = ['snapshot' => 'array', 'version_number' => 'integer'];

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
     * author.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
