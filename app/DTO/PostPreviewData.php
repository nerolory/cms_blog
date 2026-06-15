<?php

namespace App\DTO;

use App\Enums\PostEditorMode;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use App\Support\TypeCast;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;

/**
 * DTO post preview.

 *
 * @property-read string $token
 * @property-read int $userId
 * @property-read ?int $postId
 * @property-read ?int $authorUserId
 * @property-read string $title
 * @property-read string $slug
 * @property-read string $excerpt
 * @property-read string $body
 * @property-read PostStatus $status
 * @property-read string $visibility
 * @property-read ?int $requiredPermissionId
 * @property-read ?string $featuredImagePath
 * @property-read ?string $backgroundImagePath
 * @property-read ?string $themePrimaryColor
 * @property-read ?string $themeAccentColor
 * @property-read int $contentOpacity
 * @property-read PostEditorMode $editorMode
 * @property-read ?string $backUrl
 * @property-read list<string> $temporaryMediaPaths
 * @property-read ?CarbonImmutable $createdAt
 */
readonly class PostPreviewData
{
    /**
     * @param  list<string>  $temporaryMediaPaths
     */
    public function __construct(public string $token, public int $userId, public ?int $postId,
        public ?int $authorUserId, public string $title, public string $slug, public string $excerpt,
        public string $body, public PostStatus $status, public string $visibility, public ?int $requiredPermissionId,
        public ?string $featuredImagePath, public ?string $backgroundImagePath, public ?string $themePrimaryColor,
        public ?string $themeAccentColor, public int $contentOpacity, public PostEditorMode $editorMode,
        public ?string $backUrl, public array $temporaryMediaPaths = [], public ?CarbonImmutable $createdAt = null) {}

    /**
     * from post.
     *
     * @param  PostData  $data  данные формы
     * @param  int  $userId  id
     * @param  ?int  $postId  id
     * @param  ?string  $backUrl  url
     * @param  list<string>  $temporaryMediaPaths  media paths

     * @return self
     */
    public static function fromPostData(PostData $data, string $token, int $userId, ?int $postId, ?string $backUrl,
        array $temporaryMediaPaths = []): self
    {
        return new self(token: $token, userId: $userId, postId: $postId, authorUserId: $data->user_id ?? $userId,
            title: $data->title, slug: $data->slug, excerpt: $data->excerpt, body: $data->body, status: $data->status,
            visibility: $data->visibility, requiredPermissionId: $data->required_permission_id,
            featuredImagePath: $data->featured_image_path, backgroundImagePath: $data->background_image_path,
            themePrimaryColor: $data->theme_primary_color, themeAccentColor: $data->theme_accent_color,
            contentOpacity: $data->content_opacity, editorMode: $data->editor_mode, backUrl: $backUrl,
            temporaryMediaPaths: array_values($temporaryMediaPaths), createdAt: CarbonImmutable::now());
    }

    /**
     * from cache payload.
     *
     * @param  array<string, mixed>  $payload  сериализованные данные

     * @return self
     */
    public static function fromCachePayload(array $payload): self
    {
        /** @var list<string> $temporaryMediaPaths */
        $temporaryMediaPaths = TypeCast::array($payload['temporary_media_paths'] ?? []);

        $filteredPaths = array_values(array_filter(
            $temporaryMediaPaths,
            fn (mixed $path): bool => is_string($path) && $path !== '',
        ));

        return new self(
            token: TypeCast::string($payload['token'] ?? ''),
            userId: TypeCast::int($payload['user_id'] ?? 0),
            postId: TypeCast::nullableInt($payload['post_id'] ?? null),
            authorUserId: TypeCast::nullableInt($payload['author_user_id'] ?? null),
            title: TypeCast::string($payload['title'] ?? ''),
            slug: TypeCast::string($payload['slug'] ?? ''),
            excerpt: TypeCast::string($payload['excerpt'] ?? ''),
            body: TypeCast::string($payload['body'] ?? ''),
            status: PostStatus::from(TypeCast::string($payload['status'] ?? PostStatus::Draft->value)),
            visibility: TypeCast::string($payload['visibility'] ?? 'guest'),
            requiredPermissionId: TypeCast::nullableInt($payload['required_permission_id'] ?? null),
            featuredImagePath: TypeCast::nullableString($payload['featured_image_path'] ?? null),
            backgroundImagePath: TypeCast::nullableString($payload['background_image_path'] ?? null),
            themePrimaryColor: TypeCast::nullableString($payload['theme_primary_color'] ?? null),
            themeAccentColor: TypeCast::nullableString($payload['theme_accent_color'] ?? null),
            contentOpacity: TypeCast::int($payload['content_opacity'] ?? 100),
            editorMode: PostEditorMode::from(
                TypeCast::string($payload['editor_mode'] ?? PostEditorMode::Simple->value),
            ),
            backUrl: TypeCast::nullableString($payload['back_url'] ?? null),
            temporaryMediaPaths: $filteredPaths,
            createdAt: isset($payload['created_at'])
                ? CarbonImmutable::parse(TypeCast::string($payload['created_at']))
                : null,
        );
    }

    /**
     * to cache payload.
     *
     * @return array<string, mixed>
     */
    public function toCachePayload(): array
    {
        return ['token' => $this->token, 'user_id' => $this->userId, 'post_id' => $this->postId,
            'author_user_id' => $this->authorUserId, 'title' => $this->title, 'slug' => $this->slug,
            'excerpt' => $this->excerpt, 'body' => $this->body, 'status' => $this->status->value,
            'visibility' => $this->visibility, 'required_permission_id' => $this->requiredPermissionId,
            'featured_image_path' => $this->featuredImagePath, 'background_image_path' => $this->backgroundImagePath,
            'theme_primary_color' => $this->themePrimaryColor, 'theme_accent_color' => $this->themeAccentColor,
            'content_opacity' => $this->contentOpacity, 'editor_mode' => $this->editorMode->value,
            'back_url' => $this->backUrl, 'temporary_media_paths' => $this->temporaryMediaPaths,
            'created_at' => ($this->createdAt ?? CarbonImmutable::now())->toIso8601String()];
    }

    /**
     * to transient post.

     *
     * @return Post
     */
    public function toTransientPost(User $author): Post
    {
        $post = new Post(['title' => $this->title, 'slug' => $this->slug, 'excerpt' => $this->excerpt,
            'body' => $this->body, 'featured_image_path' => $this->featuredImagePath,
            'background_image_path' => $this->backgroundImagePath, 'theme_primary_color' => $this->themePrimaryColor,
            'theme_accent_color' => $this->themeAccentColor, 'content_opacity' => $this->contentOpacity,
            'editor_mode' => $this->editorMode, 'status' => $this->status, 'visibility' => $this->visibility,
            'required_permission_id' => $this->requiredPermissionId, 'is_published' => false, 'published_at' => now(),
            'user_id' => $this->authorUserId ?? $author->id]);
        $post->created_at = $this->createdAt !== null
            ? Carbon::instance($this->createdAt)
            : now();
        $post->updated_at = now();
        if ($this->postId !== null) {
            $post->id = $this->postId;
        }
        $post->setRelation('user', $author);

        return $post;
    }
}
