<?php

namespace App\Repositories\Search;

use App\Enums\SearchDriver;

/**
 * Репозиторий sphinx search backend.
 */
class SphinxSearchBackend extends AbstractExternalSearchBackend
{
    /**
     * driver.

     *
     * @return SearchDriver
     */
    public function driver(): SearchDriver
    {
        return SearchDriver::Sphinx;
    }
}
