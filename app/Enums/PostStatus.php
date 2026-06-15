<?php

namespace App\Enums;

/**
 * Перечисление post status.
 */
enum PostStatus: string
{
    case Draft = 'draft';
    case PendingModeration = 'pending_moderation';
    case Published = 'published';
    case Rejected = 'rejected';

    /**
     * Whether the author may change visibility on the web form.

     *
     * @return bool
     */
    public function allowsAuthorVisibilityEdit(): bool
    {
        return in_array($this, [self::Draft, self::PendingModeration, self::Rejected], true);
    }
}
