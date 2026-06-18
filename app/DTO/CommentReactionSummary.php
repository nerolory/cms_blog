<?php

namespace App\DTO;

use Illuminate\Support\Collection;

/**
 * Сводка реакций на комментарий.
 *
 * @property-read Collection<string, int> $counts
 * @property-read ?string $userReaction
 */
readonly class CommentReactionSummary
{
    /**
     * @param  Collection<string, int>  $counts
     */
    public function __construct(public Collection $counts, public ?string $userReaction = null) {}
}
