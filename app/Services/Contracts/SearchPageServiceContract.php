<?php

namespace App\Services\Contracts;

use App\DTO\SearchFilterOptions;

/**
 * Данные для страницы поиска (фильтры, кэш).
 */
interface SearchPageServiceContract
{
    /**
     * filter options.

     *
     * @return SearchFilterOptions
     */
    public function filterOptions(): SearchFilterOptions;
}
