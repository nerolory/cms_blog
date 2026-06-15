<?php

namespace App\Presenters;

use App\Models\Post;
use App\Repositories\Contracts\FileRepositoryContract;
use App\Support\Cache\CacheVersionManager;
use App\Support\PostTheme;
use App\Support\TypeCast;

/**
 * Presentation helpers for post media and theme styling.
 *
 * @property-read Post $post
 * @property-read FileRepositoryContract $files
 * @property-read CacheVersionManager $cacheVersions
 */
final readonly class PostPresenter
{
    public function __construct(private Post $post, private FileRepositoryContract $files,
        private CacheVersionManager $cacheVersions) {}

    /**
     * post.

     *
     * @return Post
     */
    public function post(): Post
    {
        return $this->post;
    }

    /**
     * featured image url.

     *
     * @return ?string
     */
    public function featuredImageUrl(): ?string
    {
        return $this->mediaUrl($this->post->featured_image_path);
    }

    /**
     * background image url.

     *
     * @return ?string
     */
    public function backgroundImageUrl(): ?string
    {
        return $this->mediaUrl($this->post->background_image_path);
    }

    /**
     * Проверяет наличие featured image.

     *
     * @return bool
     */
    public function hasFeaturedImage(): bool
    {
        return $this->pathExists($this->post->featured_image_path);
    }

    /**
     * Проверяет наличие background image.

     *
     * @return bool
     */
    public function hasBackgroundImage(): bool
    {
        return $this->pathExists($this->post->background_image_path);
    }

    /**
     * Проверяет наличие custom theme.

     *
     * @return bool
     */
    public function hasCustomTheme(): bool
    {
        return $this->post->theme_primary_color !== null || $this->post->theme_accent_color !== null || $this
            ->hasBackgroundImage();
    }

    /**
     * theme style attributes.
     *
     * @return array<string, mixed>
     */
    public function themeStyleAttributes(): array
    {
        $styles = [];
        if ($this->post->theme_primary_color !== null) {
            $styles['--post-primary'] = $this->post->theme_primary_color;
        }
        if ($this->post->theme_accent_color !== null) {
            $styles['--post-accent'] = $this->post->theme_accent_color;
        }
        $styles['--post-content-opacity'] = (string) (PostTheme::normalizeOpacity($this->post->content_opacity) / 100);
        if ($this->hasBackgroundImage()) {
            $backgroundUrl = $this->backgroundImageUrl();
            if ($backgroundUrl !== null) {
                $styles['background-image'] = 'url("'.$backgroundUrl.'")';
            }
        }

        return $styles;
    }

    /**
     * theme style string.

     *
     * @return string
     */
    public function themeStyleString(): string
    {
        $parts = [];
        foreach ($this->themeStyleAttributes() as $property => $value) {
            $parts[] = $property.': '.TypeCast::string($value);
        }

        return implode('; ', $parts);
    }

    private function mediaUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return $this->files->publicUrl($path, $this->cacheVersions->mediaVersionFor($path));
    }

    private function pathExists(?string $path): bool
    {
        if ($path === null || $path === '') {
            return false;
        }

        return $this->files->existsPublic($path);
    }
}
