<?php

namespace App\Support\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Versioned cache keys — bumping a version invalidates backend and browser caches together.
 */
final class CacheVersionManager
{
    public const POSTS = 'cache_version:posts';

    public const MEDIA = 'cache_version:media';

    public const PREFERENCES = 'cache_version:preferences';

    public const SEARCH = 'cache_version:search';

    /**
     * current.

     *
     * @return string
     */
    public function current(): string
    {
        return implode('.', [$this->get(self::POSTS), $this->get(self::MEDIA), $this->get(self::PREFERENCES)]);
    }

    /**
     * Возвращает .
     *
     * @param  string  $key  ключ

     * @return string
     */
    public function get(string $key): string
    {
        $value = Cache::get($key);

        return is_string($value) && $value !== '' ? $value : '1';
    }

    /**
     * bump.
     *
     * @param  string  $key  ключ

     * @return string
     */
    public function bump(string $key): string
    {
        $version = Str::uuid()->toString();
        Cache::forever($key, $version);

        return $version;
    }

    /**
     * bump posts.

     *
     * @return string
     */
    public function bumpPosts(): string
    {
        return $this->bump(self::POSTS);
    }

    /**
     * bump media.

     *
     * @return string
     */
    public function bumpMedia(): string
    {
        return $this->bump(self::MEDIA);
    }

    /**
     * bump preferences.

     *
     * @return string
     */
    public function bumpPreferences(): string
    {
        return $this->bump(self::PREFERENCES);
    }

    /**
     * bump search.

     *
     * @return string
     */
    public function bumpSearch(): string
    {
        return $this->bump(self::SEARCH);
    }

    /**
     * media version for.

     *
     * @return string
     */
    public function mediaVersionFor(?string $path): string
    {
        if ($path === null || $path === '') {
            return $this->get(self::MEDIA);
        }
        $cacheKey = self::MEDIA.':'.sha1($path);
        $value = Cache::get($cacheKey);
        if (is_string($value) && $value !== '') {
            return $value;
        }

        return $this->get(self::MEDIA);
    }

    /**
     * bump media for.

     *
     * @return string
     */
    public function bumpMediaFor(?string $path): string
    {
        $version = $this->bumpMedia();
        if ($path === null || $path === '') {
            return $version;
        }
        $pathVersion = Str::uuid()->toString();
        Cache::forever(self::MEDIA.':'.sha1($path), $pathVersion);

        return $pathVersion;
    }
}
