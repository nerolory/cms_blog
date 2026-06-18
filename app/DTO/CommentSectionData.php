<?php

namespace App\DTO;

use App\Models\PostComment;
use Illuminate\Support\Collection;

/**
 * Данные секции комментариев на странице поста.
 *
 * @property-read Collection<int, PostComment> $rootComments
 * @property-read Collection<int, int> $replyCounts
 * @property-read Collection<int, CommentReactionSummary> $reactionSummaries
 * @property-read int $totalVisibleComments
 */
readonly class CommentSectionData
{
    /**
     * @param  Collection<int, PostComment>  $rootComments
     * @param  Collection<int, int>  $replyCounts
     * @param  Collection<int, CommentReactionSummary>  $reactionSummaries
     */
    public function __construct(
        public Collection $rootComments,
        public bool $hasMoreRoots,
        public int $totalRoots,
        public int $totalVisibleComments,
        public Collection $replyCounts,
        public Collection $reactionSummaries,
    ) {}
}
