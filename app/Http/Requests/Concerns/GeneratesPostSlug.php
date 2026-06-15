<?php

namespace App\Http\Requests\Concerns;

use App\Support\PostSlugGenerator;
use App\Support\TypeCast;

/**
 * Валидация запроса generates post slug.
 */
trait GeneratesPostSlug
{
    /**
     * merge generated post slug.
     *
     * @param  ?int  $ignorePostId  post id
     */
    protected function mergeGeneratedPostSlug(?int $ignorePostId = null): void
    {
        $customSlug = TypeCast::trimToNull($this->input('slug'));
        $this->merge(['slug' => PostSlugGenerator::fromTitle($this->string('title')->toString(), $customSlug,
            $ignorePostId)]);
    }
}
