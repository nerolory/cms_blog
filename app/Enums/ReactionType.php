<?php

namespace App\Enums;

/**
 * Перечисление reaction type.
 */
enum ReactionType: string
{
    case Like = 'like';
    case Dislike = 'dislike';
    case Love = 'love';
    case Laugh = 'laugh';
    case Wow = 'wow';
    case Sad = 'sad';
    case Angry = 'angry';

    /**
     * all.
     *
     * @return list<self>
     */
    public static function all(): array
    {
        return [self::Like, self::Dislike, self::Love, self::Laugh, self::Wow, self::Sad, self::Angry];
    }

    /**
     * emoji.

     *
     * @return string
     */
    public function emoji(): string
    {
        return match ($this) {
            self::Like => '👍',
            self::Dislike => '👎',
            self::Love => '❤️',
            self::Laugh => '😂',
            self::Wow => '😮',
            self::Sad => '😢',
            self::Angry => '😡',
        };
    }

    /**
     * label.

     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Like => __('engagement.reaction.like'),
            self::Dislike => __('engagement.reaction.dislike'),
            self::Love => __('engagement.reaction.love'),
            self::Laugh => __('engagement.reaction.laugh'),
            self::Wow => __('engagement.reaction.wow'),
            self::Sad => __('engagement.reaction.sad'),
            self::Angry => __('engagement.reaction.angry'),
        };
    }
}
