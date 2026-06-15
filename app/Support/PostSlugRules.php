<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Shared validation rules for post slugs (URL segments).
 */
final class PostSlugRules
{
    public const MIN_LENGTH = 3;

    public const MAX_LENGTH = 255;

    public const FORMAT = 'regex:/^[a-z0-9_-]+$/';

    /**
     * optional.
     *
     * @return Collection<int, mixed>
     */
    public static function optional(?int $ignorePostId = null): Collection
    {
        $rules = ['nullable', 'string', 'min:'.self::MIN_LENGTH, 'max:'.self::MAX_LENGTH, self::FORMAT];
        if ($ignorePostId !== null) {
            $rules[] = Rule::unique('posts', 'slug')->ignore($ignorePostId);
        } else {
            $rules[] = Rule::unique('posts', 'slug');
        }
        /** @var Collection<int, mixed> $rulesCollection */
        $rulesCollection = collect($rules);

        return $rulesCollection;
    }
}
