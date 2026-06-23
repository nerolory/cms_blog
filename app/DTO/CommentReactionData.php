<?php

namespace App\DTO;

/**
 * DTO реакции на комментарий.

 *
 * @property-read int $commentId
 * @property-read int $userId
 * @property-read string $type
 */
readonly class CommentReactionData
{
    public function __construct(public int $commentId, public int $userId, public string $type) {}

    /**
     * from validated.
     *
     * @param  int  $commentId
     * @param  int  $userId
     * @param  string  $type
     * @return self
     */
    public static function fromValidated(int $commentId, int $userId, string $type): self
    {
        return new self(commentId: $commentId, userId: $userId, type: $type);
    }
}
