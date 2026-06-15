<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Исключение домена invalid comment.
 */
class InvalidCommentException extends RuntimeException
{
    /**
     * empty body.

     *
     * @return self
     */
    public static function emptyBody(): self
    {
        return new self(__('engagement.comment.errors.empty_body'));
    }

    /**
     * parent not found.

     *
     * @return self
     */
    public static function parentNotFound(): self
    {
        return new self(__('engagement.comment.errors.parent_not_found'));
    }

    /**
     * parent mismatch.

     *
     * @return self
     */
    public static function parentMismatch(): self
    {
        return new self(__('engagement.comment.errors.parent_mismatch'));
    }

    /**
     * max depth exceeded.

     *
     * @return self
     */
    public static function maxDepthExceeded(): self
    {
        return new self(__('engagement.comment.errors.max_depth'));
    }
}
