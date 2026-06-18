<?php

namespace App\DTO;

/**
 * DTO реакции на комментарий.
 */
readonly class CommentReactionData
{
    public function __construct(public int $commentId, public int $userId, public string $type) {}

    public static function fromValidated(int $commentId, int $userId, string $type): self
    {
        return new self(commentId: $commentId, userId: $userId, type: $type);
    }
}
