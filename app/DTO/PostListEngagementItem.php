<?php

namespace App\DTO;

use Illuminate\Support\Collection;

/**
 * Сводка просмотров и реакций для карточки поста в списке.
 *
 * @property-read int $viewsCount
 * @property-read Collection<string, int> $reactionCounts
 */
readonly class PostListEngagementItem
{
    /**
     * @param  Collection<string, int>  $reactionCounts  Только реакции с count &gt; 0
     */
    public function __construct(public int $viewsCount, public Collection $reactionCounts) {}
}
