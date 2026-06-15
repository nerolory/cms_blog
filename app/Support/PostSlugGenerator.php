<?php

namespace App\Support;

use App\Models\Post;
use Illuminate\Support\Str;

/**
 * Builds unique SEO-friendly post slugs from titles.
 */
final class PostSlugGenerator
{
    /**
     * from title.
     *
     * @param  ?string  $customSlug  slug
     * @param  ?int  $ignorePostId  post id

     * @return string
     */
    public static function fromTitle(string $title, ?string $customSlug = null, ?int $ignorePostId = null): string
    {
        $base = $customSlug !== null && $customSlug !== '' ? strtolower($customSlug) : self::toSlug($title);
        if (! self::exists($base, $ignorePostId)) {
            return $base;
        }
        $maxId = Post::withTrashed()->max('id');
        $suffix = TypeCast::int($maxId) + 1;
        $candidate = $base.'-'.$suffix;
        while (self::exists($candidate, $ignorePostId)) {
            $suffix++;
            $candidate = $base.'-'.$suffix;
        }

        return $candidate;
    }

    private static function toSlug(string $value): string
    {
        $slug = Str::slug($value);

        return $slug !== '' ? $slug : 'post';
    }

    private static function exists(string $slug, ?int $ignorePostId): bool
    {
        $query = Post::withTrashed()->where('slug', $slug);
        if ($ignorePostId !== null) {
            $query->whereKeyNot($ignorePostId);
        }

        return $query->exists();
    }
}
