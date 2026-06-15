<?php

namespace App\DTO;

use App\Enums\PostEditorMode;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Exceptions\InvalidPostDataException;
use App\Models\Post;
use App\Support\HtmlSanitizer;
use App\Support\PostTheme;
use App\Support\TypeCast;
use Illuminate\Support\Collection;

/**
 * DTO post.

 *
 * @property-read string $title
 * @property-read string $slug
 * @property-read string $excerpt
 * @property-read string $body
 * @property-read bool $is_published
 * @property-read ?int $user_id
 * @property-read PostStatus $status
 * @property-read string $visibility
 * @property-read ?int $required_permission_id
 * @property-read ?string $rejection_reason
 * @property-read ?string $featured_image_path
 * @property-read ?string $background_image_path
 * @property-read ?string $theme_primary_color
 * @property-read ?string $theme_accent_color
 * @property-read int $content_opacity
 * @property-read PostEditorMode $editor_mode
 * @property-read ?int $category_id
 * @property-read Collection<int, int> $tag_ids
 */
readonly class PostData extends AbstractData
{
    /**
     * @param  Collection<int, int>  $tag_ids
     */
    public function __construct(public string $title, public string $slug, public string $excerpt, public string $body,
        public bool $is_published, public ?int $user_id, public PostStatus $status, public string $visibility,
        public ?int $required_permission_id = null, public ?string $rejection_reason = null,
        public ?string $featured_image_path = null, public ?string $background_image_path = null,
        public ?string $theme_primary_color = null, public ?string $theme_accent_color = null,
        public int $content_opacity = PostTheme::MAX_CONTENT_OPACITY,
        public PostEditorMode $editor_mode = PostEditorMode::Simple, public ?int $category_id = null,
        public Collection $tag_ids = new Collection) {}

    /**
     * Builds DTO from Form Request validated payload.
     *
     *
     * @param  Collection<int, int>  $tagIds
     * @return self
     *
     * @throws InvalidPostDataException
     */
    public static function fromValidated(string $title, string $slug, string $excerpt, string $body, bool $isPublished,
        ?int $userId, PostStatus $status = PostStatus::Draft, string $visibility = 'guest',
        ?int $requiredPermissionId = null, ?string $featuredImagePath = null, ?string $backgroundImagePath = null,
        ?string $themePrimaryColor = null, ?string $themeAccentColor = null,
        int $contentOpacity = PostTheme::MAX_CONTENT_OPACITY, PostEditorMode $editorMode = PostEditorMode::Simple,
        ?int $categoryId = null, Collection $tagIds = new Collection): self
    {
        $title = TypeCast::trimRequired($title);
        $slug = TypeCast::trimRequired($slug);
        $excerpt = TypeCast::trimRequired($excerpt);
        $body = HtmlSanitizer::sanitize(TypeCast::trimRequired($body), $editorMode);
        $themeValues = PostTheme::normalize($themePrimaryColor, $themeAccentColor, $contentOpacity);
        $themePrimaryColor = $themeValues->primaryColor;
        $themeAccentColor = $themeValues->accentColor;
        $contentOpacity = $themeValues->contentOpacity;
        if ($title === '') {
            throw InvalidPostDataException::emptyTitle();
        }
        if ($slug === '') {
            throw InvalidPostDataException::emptySlug();
        }
        if ($body === '') {
            throw InvalidPostDataException::emptyBody();
        }
        self::assertValidVisibility($visibility, $requiredPermissionId);

        return new self(title: $title, slug: $slug, excerpt: $excerpt, body: $body, is_published: $isPublished,
            user_id: $userId, status: $status, visibility: $visibility, required_permission_id: $requiredPermissionId,
            featured_image_path: $featuredImagePath, background_image_path: $backgroundImagePath,
            theme_primary_color: $themePrimaryColor, theme_accent_color: $themeAccentColor,
            content_opacity: $contentOpacity, editor_mode: $editorMode, category_id: $categoryId, tag_ids: $tagIds);
    }

    /**
     * DTO for web/API update — blank optional strings keep existing post values.
     *
     *
     * @param  Collection<int, int>  $tagIds
     * @return self
     *
     * @throws InvalidPostDataException
     */
    public static function fromValidatedUpdate(string $title, mixed $slug, mixed $excerpt, string $body,
        bool $isPublished, ?int $userId, PostStatus $status, string $visibility, ?int $requiredPermissionId,
        Post $existing, ?string $themePrimaryColor = null, ?string $themeAccentColor = null,
        int $contentOpacity = PostTheme::MAX_CONTENT_OPACITY, PostEditorMode $editorMode = PostEditorMode::Simple,
        ?int $categoryId = null, Collection $tagIds = new Collection): self
    {
        return self::fromValidated(title: $title, slug: TypeCast::trimOrKeep($slug, $existing->slug),
            excerpt: TypeCast::trimOrKeep($excerpt, $existing->excerpt ?? ''), body: $body, isPublished: $isPublished,
            userId: $userId ?? $existing->user_id, status: $status, visibility: $visibility,
            requiredPermissionId: $requiredPermissionId, featuredImagePath: $existing->featured_image_path,
            backgroundImagePath: $existing->background_image_path, themePrimaryColor: $themePrimaryColor,
            themeAccentColor: $themeAccentColor, contentOpacity: $contentOpacity, editorMode: $editorMode,
            categoryId: $categoryId ?? $existing->category_id, tagIds: $tagIds);
    }

    /**
     * DTO for author submission (pending moderation).
     *
     *
     * @param  Collection<int, int>  $tagIds
     * @return self
     *
     * @throws InvalidPostDataException
     */
    public static function forAuthorSubmission(string $title, string $slug, string $excerpt, string $body, int $userId,
        string $visibility = 'guest', ?int $requiredPermissionId = null, ?string $themePrimaryColor = null,
        ?string $themeAccentColor = null, int $contentOpacity = PostTheme::MAX_CONTENT_OPACITY,
        PostEditorMode $editorMode = PostEditorMode::Simple, ?int $categoryId = null,
        Collection $tagIds = new Collection): self
    {
        return self::fromValidated(title: $title, slug: $slug, excerpt: $excerpt, body: $body, isPublished: false,
            userId: $userId, status: PostStatus::PendingModeration, visibility: $visibility,
            requiredPermissionId: $requiredPermissionId, themePrimaryColor: $themePrimaryColor,
            themeAccentColor: $themeAccentColor, contentOpacity: $contentOpacity, editorMode: $editorMode,
            categoryId: $categoryId, tagIds: $tagIds);
    }

    /**
     * DTO for Filament admin (create/update via PostService).
     *
     * @param  array<string, mixed>  $data
     * @return self
     *
     * @throws InvalidPostDataException
     */
    public static function fromFilament(array $data): self
    {
        return FilamentPostFormState::fromArray($data)->toPostData();
    }

    /**
     * Builds DTO from normalized Filament payload (used by FilamentPostFormState).
     *
     * @param  array<string, mixed>  $data
     * @return self
     *
     * @throws InvalidPostDataException
     */
    public static function fromFilamentPayload(array $data): self
    {
        $status = $data['status'] instanceof PostStatus
            ? $data['status']
            : PostStatus::from(TypeCast::string($data['status']));
        $editorMode = $data['editor_mode'] instanceof PostEditorMode
            ? $data['editor_mode']
            : PostEditorMode::from(TypeCast::string($data['editor_mode'] ?? PostEditorMode::Simple->value));
        $title = TypeCast::trimRequired($data['title']);
        $slug = TypeCast::trimRequired($data['slug']);
        $excerpt = TypeCast::trimRequired($data['excerpt'] ?? '');
        $body = HtmlSanitizer::sanitize(TypeCast::trimRequired($data['body']), $editorMode);
        $visibility = TypeCast::string($data['visibility'] ?? PostVisibility::Guest->value);
        $requiredPermissionId = isset($data['required_permission_id'])
            ? TypeCast::nullableInt($data['required_permission_id'])
            : null;
        $themePrimaryColor = TypeCast::nullableString($data['theme_primary_color'] ?? null);
        $themeAccentColor = TypeCast::nullableString($data['theme_accent_color'] ?? null);
        $contentOpacity = TypeCast::int($data['content_opacity'] ?? PostTheme::MAX_CONTENT_OPACITY);
        if ($title === '') {
            throw InvalidPostDataException::emptyTitle();
        }
        if ($slug === '') {
            throw InvalidPostDataException::emptySlug();
        }
        if ($body === '') {
            throw InvalidPostDataException::emptyBody();
        }
        self::assertValidVisibility($visibility, $requiredPermissionId);
        $categoryId = isset($data['category_id']) ? TypeCast::nullableInt($data['category_id']) : null;
        $tagIds = self::normalizeTagIds($data['tag_ids'] ?? null);
        if ($categoryId === null) {
            throw InvalidPostDataException::missingCategory();
        }

        return new self(
            title: $title,
            slug: $slug,
            excerpt: $excerpt,
            body: $body,
            is_published: $status === PostStatus::Published,
            user_id: isset($data['user_id']) ? TypeCast::nullableInt($data['user_id']) : null,
            status: $status,
            visibility: $visibility,
            required_permission_id: $requiredPermissionId,
            rejection_reason: isset($data['rejection_reason'])
                ? TypeCast::nullableString($data['rejection_reason'])
                : null,
            featured_image_path: TypeCast::nullableString($data['featured_image_path'] ?? null),
            background_image_path: TypeCast::nullableString($data['background_image_path'] ?? null),
            theme_primary_color: $themePrimaryColor,
            theme_accent_color: $themeAccentColor,
            content_opacity: $contentOpacity,
            editor_mode: $editorMode,
            category_id: $categoryId,
            tag_ids: $tagIds,
        );
    }

    /**
     * to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $visibility = PostVisibility::tryFrom($this->visibility);

        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'user_id' => $this->user_id,
            'status' => $this->status->value,
            'visibility' => $this->visibility,
            'required_permission_id' => $visibility?->requiresPermission() === true
                ? $this->required_permission_id
                : null,
            'rejection_reason' => $this->rejection_reason,
            'is_published' => $this->status === PostStatus::Published,
            'featured_image_path' => $this->featured_image_path,
            'background_image_path' => $this->background_image_path,
            'theme_primary_color' => $this->theme_primary_color,
            'theme_accent_color' => $this->theme_accent_color,
            'content_opacity' => $this->content_opacity,
            'editor_mode' => $this->editor_mode->value,
            'category_id' => $this->category_id,
        ];
    }

    /**
     * with featured image path.

     *
     * @return self
     */
    public function withFeaturedImagePath(?string $path): self
    {
        return new self(title: $this->title, slug: $this->slug, excerpt: $this->excerpt, body: $this->body,
            is_published: $this->is_published, user_id: $this->user_id, status: $this->status,
            visibility: $this->visibility, required_permission_id: $this->required_permission_id,
            rejection_reason: $this->rejection_reason, featured_image_path: $path,
            background_image_path: $this->background_image_path, theme_primary_color: $this->theme_primary_color,
            theme_accent_color: $this->theme_accent_color, content_opacity: $this->content_opacity,
            editor_mode: $this->editor_mode);
    }

    /**
     * with background image path.

     *
     * @return self
     */
    public function withBackgroundImagePath(?string $path): self
    {
        return new self(title: $this->title, slug: $this->slug, excerpt: $this->excerpt, body: $this->body,
            is_published: $this->is_published, user_id: $this->user_id, status: $this->status,
            visibility: $this->visibility, required_permission_id: $this->required_permission_id,
            rejection_reason: $this->rejection_reason, featured_image_path: $this->featured_image_path,
            background_image_path: $path, theme_primary_color: $this->theme_primary_color,
            theme_accent_color: $this->theme_accent_color, content_opacity: $this->content_opacity,
            editor_mode: $this->editor_mode);
    }

    /**
     * @return Collection<int, int>
     */
    private static function normalizeTagIds(mixed $tagIds): Collection
    {
        if ($tagIds instanceof Collection) {
            /** @var Collection<int, int> */
            return $tagIds->map(fn (mixed $id): int => TypeCast::int($id))->filter(fn (int $id): bool => $id > 0)
                ->values();
        }
        if (! is_array($tagIds)) {
            return collect();
        }

        /** @var Collection<int, int> */
        return collect($tagIds)->map(fn (mixed $id): int => TypeCast::int($id))->filter(fn (int $id): bool => $id > 0)
            ->values();
    }

    /**
     * @throws InvalidPostDataException
     */
    private static function assertValidVisibility(string $visibility, ?int $requiredPermissionId): void
    {
        $enum = PostVisibility::tryFrom($visibility);
        if ($enum === null) {
            throw InvalidPostDataException::invalidVisibility($visibility);
        }
        if ($enum->requiresPermission() && $requiredPermissionId === null) {
            throw InvalidPostDataException::missingPermissionForVisibility();
        }
        if (! $enum->requiresPermission() && $requiredPermissionId !== null) {
            throw InvalidPostDataException::unexpectedPermissionForVisibility();
        }
    }
}
