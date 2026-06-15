<?php

namespace App\Enums;

/**
 * Перечисление post moderation action.
 */
enum PostModerationAction: string
{
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Updated = 'updated';
    case Restored = 'restored';
}
