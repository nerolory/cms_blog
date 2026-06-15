<?php

namespace App\Services\Search;

use App\Enums\SearchDriver;
use App\Repositories\Contracts\SearchSettingsRepositoryContract;
use App\Repositories\Search\AiSearchBackend;
use App\Repositories\Search\Contracts\SearchBackendContract;
use App\Repositories\Search\DatabaseSearchBackend;
use App\Repositories\Search\ElasticsearchSearchBackend;
use App\Repositories\Search\SolrSearchBackend;
use App\Repositories\Search\SphinxSearchBackend;

/**
 * Сервис search backend resolver.

 *
 * @property-read SearchSettingsRepositoryContract $searchSettingsRepository
 */
final class SearchBackendResolver
{
    /**
     * @var array<string, SearchBackendContract>
     */
    private array $backends;

    public function __construct(private SearchSettingsRepositoryContract $searchSettingsRepository,
        DatabaseSearchBackend $database, ElasticsearchSearchBackend $elasticsearch, SphinxSearchBackend $sphinx,
        SolrSearchBackend $solr, AiSearchBackend $ai)
    {
        $this->backends = [SearchDriver::Database->value => $database,
            SearchDriver::Elasticsearch->value => $elasticsearch, SearchDriver::Sphinx->value => $sphinx,
            SearchDriver::Solr->value => $solr, SearchDriver::Ai->value => $ai];
    }

    /**
     * configured driver.

     *
     * @return SearchDriver
     */
    public function configuredDriver(): SearchDriver
    {
        return $this->searchSettingsRepository->getSearchSettings()->driver;
    }

    /**
     * resolve.

     *
     * @return SearchBackendContract
     */
    public function resolve(): SearchBackendContract
    {
        $configured = $this->configuredDriver();
        $backend = $this->backends[$configured->value] ?? $this->backends[SearchDriver::Database->value];
        if ($backend->isAvailable()) {
            return $backend;
        }

        return $this->backends[SearchDriver::Database->value];
    }

    /**
     * resolved driver.

     *
     * @return SearchDriver
     */
    public function resolvedDriver(): SearchDriver
    {
        return $this->resolve()->driver();
    }
}
