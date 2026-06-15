<?php

namespace App\Enums;

/**
 * Перечисление comment status.
 */
enum CommentStatus: string
{
    case Visible = 'visible';
    case Hidden = 'hidden';

    /**
     * label.

     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Visible => __('engagement.comment.status.visible'),
            self::Hidden => __('engagement.comment.status.hidden'),
        };
    }
}
