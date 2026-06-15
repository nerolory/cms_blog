<?php

namespace App\DTO;

use App\Enums\PostEditorMode;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Support\FilamentPostBody;
use App\Support\PostTheme;
use App\Support\TypeCast;
use DateTimeInterface;
use Illuminate\Support\Collection;

/**
 * DTO filament post form state.

 *
 * @property-read string $title
 * @property-read string $slug
 * @property-read string $excerpt
 * @property-read mixed $body
 * @property-read mixed $bodyHtml
 * @property-read PostEditorMode $editorMode
 * @property-read PostStatus $status
 * @property-read string $visibility
 * @property-read ?int $userId
 * @property-read ?int $requiredPermissionId
 * @property-read ?string $rejectionReason
 * @property-read ?string $featuredImagePath
 * @property-read ?string $backgroundImagePath
 * @property-read ?string $themePrimaryColor
 * @property-read ?string $themeAccentColor
 * @property-read int $contentOpacity
 * @property-read ?DateTimeInterface $publishedAt
 * @property-read ?string $metaTitle
 * @property-read ?string $metaDescription
 * @property-read ?string $ogImagePath
 * @property-read ?string $canonicalUrl
 * @property-read ?string $robots
 * @property-read ?int $categoryId
 * @property-read Collection<int, int> $tagIds
 */
readonly class FilamentPostFormState extends AbstractData
{
    public function __construct(
        public string $title,
        public string $slug,
        public string $excerpt,
        public mixed $body,
        public mixed $bodyHtml,
        public PostEditorMode $editorMode,
        public PostStatus $status,
        public string $visibility,
        public ?int $userId,
        public ?int $requiredPermissionId,
        public ?string $rejectionReason,
        public ?string $featuredImagePath,
        public ?string $backgroundImagePath,
        public ?string $themePrimaryColor,
        public ?string $themeAccentColor,
        public int $contentOpacity,
        public ?DateTimeInterface $publishedAt,
        public ?string $metaTitle,
        public ?string $metaDescription,
        public ?string $ogImagePath,
        public ?string $canonicalUrl,
        public ?string $robots,
        public ?int $categoryId,
        /** @var Collection<int, int> */
        public Collection $tagIds
    ) {}

    /**
     * from array.
     *
     * @param  array<string, mixed>  $data  данные формы

     * @return self
     */
    public static function fromArray(array $data): self
    {
        $status = $data['status'] instanceof PostStatus
            ? $data['status']
            : PostStatus::from(TypeCast::string($data['status'] ?? PostStatus::Draft->value));
        $editorMode = $data['editor_mode'] instanceof PostEditorMode
            ? $data['editor_mode']
            : PostEditorMode::from(TypeCast::string($data['editor_mode'] ?? PostEditorMode::Simple->value));
        $themeValues = PostTheme::normalize(TypeCast::nullableString($data['theme_primary_color'] ?? null),
            TypeCast::nullableString($data['theme_accent_color'] ?? null),
            $data['content_opacity'] ?? PostTheme::MAX_CONTENT_OPACITY);
        $publishedAt = $data['published_at'] ?? null;
        if ($publishedAt !== null && ! $publishedAt instanceof DateTimeInterface) {
            $publishedAt = null;
        }

        return new self(
            title: TypeCast::trimRequired($data['title'] ?? ''),
            slug: TypeCast::trimRequired($data['slug'] ?? ''),
            excerpt: TypeCast::trimRequired($data['excerpt'] ?? ''),
            body: $data['body'] ?? null,
            bodyHtml: $data['body_html'] ?? null,
            editorMode: $editorMode,
            status: $status,
            visibility: TypeCast::string($data['visibility'] ?? PostVisibility::Guest->value),
            userId: isset($data['user_id']) ? TypeCast::nullableInt($data['user_id']) : null,
            requiredPermissionId: isset($data['required_permission_id'])
                ? TypeCast::nullableInt($data['required_permission_id'])
                : null,
            rejectionReason: isset($data['rejection_reason'])
                ? TypeCast::nullableString($data['rejection_reason'])
                : null,
            featuredImagePath: TypeCast::nullableString($data['featured_image_path'] ?? null),
            backgroundImagePath: TypeCast::nullableString($data['background_image_path'] ?? null),
            themePrimaryColor: $themeValues->primaryColor,
            themeAccentColor: $themeValues->accentColor,
            contentOpacity: $themeValues->contentOpacity,
            publishedAt: $publishedAt,
            metaTitle: self::nullableTrimmed($data['meta_title'] ?? null),
            metaDescription: self::nullableTrimmed($data['meta_description'] ?? null),
            ogImagePath: TypeCast::nullableString($data['og_image_path'] ?? null),
            canonicalUrl: self::nullableTrimmed($data['canonical_url'] ?? null),
            robots: self::nullableTrimmed($data['robots'] ?? null),
            categoryId: isset($data['category_id']) ? TypeCast::nullableInt($data['category_id']) : null,
            tagIds: self::tagIdsFromMixed($data['tags'] ?? $data['tag_ids'] ?? []),
        );
    }

    /**
     * @return Collection<int, int>
     */
    private static function tagIdsFromMixed(mixed $value): Collection
    {
        if ($value instanceof Collection) {
            /** @var Collection<int, int> $tagIds */
            $tagIds = $value->map(fn (mixed $id): int => TypeCast::nullableInt($id) ?? 0)
                ->filter(fn (int $id): bool => $id > 0)->values();

            return $tagIds;
        }
        if (! is_array($value)) {
            return collect();
        }
        /** @var Collection<int, int> $tagIds */
        $tagIds = collect($value)->map(fn (mixed $id): int => TypeCast::nullableInt($id) ?? 0)
            ->filter(fn (int $id): bool => $id > 0)->values();

        return $tagIds;
    }

    /**
     * to post.

     *
     * @return PostData
     */
    public function toPostData(): PostData
    {
        $payload = ['title' => $this->title, 'slug' => $this->slug, 'excerpt' => $this->excerpt,
            'body' => FilamentPostBody::resolveFromFilament(['body' => $this->body, 'body_html' => $this->bodyHtml],
                $this->editorMode), 'status' => $this->status, 'editor_mode' => $this->editorMode,
            'visibility' => $this->visibility, 'user_id' => $this->userId,
            'required_permission_id' => $this->requiredPermissionId, 'rejection_reason' => $this->rejectionReason,
            'featured_image_path' => $this->featuredImagePath, 'background_image_path' => $this->backgroundImagePath,
            'theme_primary_color' => $this->themePrimaryColor, 'theme_accent_color' => $this->themeAccentColor,
            'content_opacity' => $this->contentOpacity, 'category_id' => $this->categoryId, 'tag_ids' => $this->tagIds];

        return PostData::fromFilamentPayload($payload);
    }

    /**
     * to seo.

     *
     * @return SeoData
     */
    public function toSeoData(): SeoData
    {
        return new SeoData(metaTitle: $this->metaTitle, metaDescription: $this->metaDescription,
            ogImagePath: $this->ogImagePath, canonicalUrl: $this->canonicalUrl, robots: $this->robots);
    }

    private static function nullableTrimmed(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim(TypeCast::string($value));

        return $trimmed === '' ? null : $trimmed;
    }
}
