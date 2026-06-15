<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when post DTO invariants are violated.
 */
class InvalidPostDataException extends RuntimeException
{
    /**
     * empty title.

     *
     * @return self
     */
    public static function emptyTitle(): self
    {
        return new self(__('posts.exceptions.empty_title'));
    }

    /**
     * empty slug.

     *
     * @return self
     */
    public static function emptySlug(): self
    {
        return new self(__('posts.exceptions.empty_slug'));
    }

    /**
     * empty body.

     *
     * @return self
     */
    public static function emptyBody(): self
    {
        return new self(__('posts.exceptions.empty_body'));
    }

    /**
     * invalid visibility.

     *
     * @return self
     */
    public static function invalidVisibility(string $visibility): self
    {
        return new self(__('posts.exceptions.invalid_visibility', ['visibility' => $visibility]));
    }

    /**
     * missing permission for visibility.

     *
     * @return self
     */
    public static function missingPermissionForVisibility(): self
    {
        return new self(__('posts.exceptions.missing_permission'));
    }

    /**
     * unexpected permission for visibility.

     *
     * @return self
     */
    public static function unexpectedPermissionForVisibility(): self
    {
        return new self(__('posts.exceptions.unexpected_permission'));
    }

    /**
     * missing category.

     *
     * @return self
     */
    public static function missingCategory(): self
    {
        return new self(__('posts.exceptions.missing_category'));
    }
}
