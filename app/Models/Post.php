<?php

namespace App\Models;

use App\Enums\PostEditorMode;
use App\Enums\PostStatus;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;

/**
 * Eloquent-модель поста блога.
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $category_id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string $body
 * @property string|null $featured_image_path
 * @property string|null $background_image_path
 * @property string|null $theme_primary_color
 * @property string|null $theme_accent_color
 * @property int $content_opacity
 * @property PostEditorMode $editor_mode
 * @property PostStatus $status
 * @property string $visibility
 * @property int|null $required_permission_id
 * @property string|null $rejection_reason
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon|null $scheduled_publish_at
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $og_image_path
 * @property string|null $canonical_url
 * @property string|null $robots
 * @property int $views_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $user
 * @property-read Permission|null $requiredPermission
 * @property-read Category|null $category
 * @property-read Collection<int, Tag> $tags
 * @property-read Collection<int, PostComment> $comments
 * @property-read Collection<int, PostReaction> $reactions
 * @property-read Collection<int, PostVersion> $versions
 * @property-read Collection<int, PostModerationLog> $moderationLogs
 */
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'slug', 'excerpt', 'body', 'featured_image_path', 'background_image_path',
        'theme_primary_color', 'theme_accent_color', 'content_opacity', 'editor_mode', 'status', 'visibility',
        'required_permission_id', 'rejection_reason', 'is_published', 'published_at', 'scheduled_publish_at',
        'category_id', 'meta_title', 'meta_description', 'og_image_path', 'canonical_url', 'robots'];

    protected $casts = ['status' => PostStatus::class, 'editor_mode' => PostEditorMode::class,
        'is_published' => 'boolean', 'published_at' => 'datetime', 'scheduled_publish_at' => 'datetime',
        'views_count' => 'integer', 'content_opacity' => 'integer'];

    /**
     * Автор поста.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Право доступа для ограниченной видимости.
     *
     * @return BelongsTo<Permission, $this>
     */
    public function requiredPermission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'required_permission_id');
    }

    /**
     * Журнал модерации поста.
     *
     * @return HasMany<PostModerationLog, $this>
     */
    public function moderationLogs(): HasMany
    {
        return $this->hasMany(PostModerationLog::class);
    }

    /**
     * Версии контента поста.
     *
     * @return HasMany<PostVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(PostVersion::class)->orderByDesc('version_number');
    }

    /**
     * Категория поста.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Теги поста.
     *
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Комментарии к посту.
     *
     * @return HasMany<PostComment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class);
    }

    /**
     * Реакции на пост.
     *
     * @return HasMany<PostReaction, $this>
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(PostReaction::class);
    }

    /**
     * booted.
     */
    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            $post->is_published = $post->status === PostStatus::Published;
        });
    }

    /**
     * Проверяет owned by.

     *
     * @return bool
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /**
     * Возвращает route key name.

     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
