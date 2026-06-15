<?php

namespace App\DTO;

use App\Enums\PostEditorMode;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Support\TypeCast;

/**
 * DTO post version snapshot.

 *
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
 * @property-read ?int $userId
 */
readonly class PostVersionSnapshot
{
    public function __construct(public string $title, public string $slug, public string $excerpt, public string $body,
        public PostStatus $status, public string $visibility, public ?int $requiredPermissionId,
        public ?string $featuredImagePath, public ?string $backgroundImagePath, public ?string $themePrimaryColor,
        public ?string $themeAccentColor, public int $contentOpacity, public PostEditorMode $editorMode,
        public ?int $userId) {}

    /**
     * from post.
     *
     * @param  Post  $post  пост

     * @return self
     */
    public static function fromPost(Post $post): self
    {
        return new self(title: $post->title, slug: $post->slug, excerpt: $post->excerpt ?? '', body: $post->body,
            status: $post->status, visibility: $post->visibility, requiredPermissionId: $post->required_permission_id,
            featuredImagePath: $post->featured_image_path, backgroundImagePath: $post->background_image_path,
            themePrimaryColor: $post->theme_primary_color, themeAccentColor: $post->theme_accent_color,
            contentOpacity: (int) $post->content_opacity, editorMode: $post->editor_mode, userId: $post->user_id);
    }

    /**
     * from array.
     *
     * @param  array<string, mixed>  $payload  сериализованные данные

     * @return self
     */
    public static function fromArray(array $payload): self
    {
        return new self(
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
            userId: TypeCast::nullableInt($payload['user_id'] ?? null),
        );
    }

    /**
     * to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['title' => $this->title, 'slug' => $this->slug, 'excerpt' => $this->excerpt, 'body' => $this->body,
            'status' => $this->status->value, 'visibility' => $this->visibility,
            'required_permission_id' => $this->requiredPermissionId, 'featured_image_path' => $this->featuredImagePath,
            'background_image_path' => $this->backgroundImagePath, 'theme_primary_color' => $this->themePrimaryColor,
            'theme_accent_color' => $this->themeAccentColor, 'content_opacity' => $this->contentOpacity,
            'editor_mode' => $this->editorMode->value, 'user_id' => $this->userId];
    }

    /**
     * to post.

     *
     * @return PostData
     */
    public function toPostData(Post $existing): PostData
    {
        return PostData::fromValidated(title: $this->title, slug: $this->slug, excerpt: $this->excerpt,
            body: $this->body, isPublished: $this->status === PostStatus::Published,
            userId: $this->userId ?? $existing->user_id, status: $this->status, visibility: $this->visibility,
            requiredPermissionId: $this->requiredPermissionId, featuredImagePath: $this->featuredImagePath,
            backgroundImagePath: $this->backgroundImagePath, themePrimaryColor: $this->themePrimaryColor,
            themeAccentColor: $this->themeAccentColor, contentOpacity: $this->contentOpacity,
            editorMode: $this->editorMode);
    }
}
