<?php

namespace App\DTO;

use App\Models\PostComment;
use Illuminate\Support\Collection;

/**
 * DTO post engagement.

 *
 * @property-read int $viewsCount
 * @property-read Collection<int, PostComment> $rootComments
 * @property-read Collection<string, int> $reactionCounts
 * @property-read ?string $userReaction
 */
readonly class PostEngagementData
{
    /**
     * @param  Collection<int, PostComment>  $rootComments
     * @param  Collection<string, int>  $reactionCounts
     */
    public function __construct(public int $viewsCount, public Collection $rootComments,
        public Collection $reactionCounts, public ?string $userReaction = null) {}
}
