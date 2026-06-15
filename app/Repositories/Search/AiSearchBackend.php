<?php

namespace App\Repositories\Search;

use App\Enums\SearchDriver;

/**
 * Репозиторий ai search backend.
 */
class AiSearchBackend extends AbstractExternalSearchBackend
{
    /**
     * driver.

     *
     * @return SearchDriver
     */
    public function driver(): SearchDriver
    {
        return SearchDriver::Ai;
    }
}
