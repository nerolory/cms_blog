<?php

namespace App\Repositories\Search;

use App\Enums\SearchDriver;

/**
 * Репозиторий solr search backend.
 */
class SolrSearchBackend extends AbstractExternalSearchBackend
{
    /**
     * driver.

     *
     * @return SearchDriver
     */
    public function driver(): SearchDriver
    {
        return SearchDriver::Solr;
    }
}
