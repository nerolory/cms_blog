<?php

namespace App\DTO;

/**
 * DTO comment.

 *
 * @property-read int $postId
 * @property-read int $userId
 * @property-read string $body
 * @property-read ?int $parentId
 */
readonly class CommentData
{
    public function __construct(public int $postId, public int $userId, public string $body,
        public ?int $parentId = null) {}

    /**
     * from validated.
     *
     * @param  int  $postId  id
     * @param  int  $userId  id
     * @param  ?int  $parentId  id

     * @return self
     */
    public static function fromValidated(int $postId, int $userId, string $body, ?int $parentId = null): self
    {
        return new self(postId: $postId, userId: $userId, body: trim($body), parentId: $parentId);
    }
}
