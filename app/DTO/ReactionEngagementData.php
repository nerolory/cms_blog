<?php

namespace App\DTO;

use Illuminate\Support\Collection;

/**
 * Агрегированные реакции на пост и реакция текущего
 * пользователя.

 *
 * @property-read Collection<string, int> $counts
 * @property-read ?string $userReaction
 */
readonly class ReactionEngagementData
{
    /**
     * @param  Collection<string, int>  $counts
     */
    public function __construct(
        public Collection $counts,
        public ?string $userReaction,
    ) {}
}
