<?php

namespace App\DTO;

use Illuminate\Support\Collection;

/**
 * DTO post engagement.
 *
 * @property-read Collection<string, int> $reactionCounts
 */
readonly class PostEngagementData
{
    /**
     * @param  Collection<string, int>  $reactionCounts
     */
    public function __construct(
        public int $viewsCount,
        public CommentSectionData $comments,
        public Collection $reactionCounts,
        public ?string $userReaction = null,
    ) {}
}
