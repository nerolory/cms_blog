<?php

namespace App\Providers;

use App\Repositories\Contracts\SearchSettingsRepositoryContract;
use App\Repositories\Search\AiSearchBackend;
use App\Repositories\Search\DatabaseSearchBackend;
use App\Repositories\Search\ElasticsearchSearchBackend;
use App\Repositories\Search\SolrSearchBackend;
use App\Repositories\Search\SphinxSearchBackend;
use App\Repositories\SearchSettingsRepository;
use App\Services\Contracts\SearchSettingsServiceContract;
use App\Services\Search\SearchBackendResolver;
use App\Services\SearchSettingsService;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider search service provider.
 */
class SearchServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует сервисы контейнера.
     */
    public function register(): void
    {
        $this->app->singleton(DatabaseSearchBackend::class);
        $this->app->singleton(ElasticsearchSearchBackend::class);
        $this->app->singleton(SphinxSearchBackend::class);
        $this->app->singleton(SolrSearchBackend::class);
        $this->app->singleton(AiSearchBackend::class);
        $this->app->singleton(SearchBackendResolver::class);
        $this->app->bind(SearchSettingsRepositoryContract::class, SearchSettingsRepository::class);
        $this->app->bind(SearchSettingsServiceContract::class, SearchSettingsService::class);
    }
}
