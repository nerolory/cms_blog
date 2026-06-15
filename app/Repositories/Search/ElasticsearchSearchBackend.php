<?php

namespace App\Repositories\Search;

use App\Enums\SearchDriver;

/**
 * Репозиторий elasticsearch search backend.
 */
class ElasticsearchSearchBackend extends AbstractExternalSearchBackend
{
    /**
     * driver.

     *
     * @return SearchDriver
     */
    public function driver(): SearchDriver
    {
        return SearchDriver::Elasticsearch;
    }
}
