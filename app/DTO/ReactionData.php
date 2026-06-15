<?php

namespace App\DTO;

/**
 * DTO reaction.

 *
 * @property-read int $postId
 * @property-read int $userId
 * @property-read string $type
 */
readonly class ReactionData
{
    public function __construct(public int $postId, public int $userId, public string $type) {}

    /**
     * from validated.
     *
     * @param  int  $postId  id
     * @param  int  $userId  id

     * @return self
     */
    public static function fromValidated(int $postId, int $userId, string $type): self
    {
        return new self(postId: $postId, userId: $userId, type: $type);
    }
}
