<?php

namespace App\Providers;

use App\Listeners\RecordQueueWorkerHeartbeatListener;
use App\Repositories\AuthorRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CommentRepository;
use App\Repositories\Contracts\AuthorRepositoryContract;
use App\Repositories\Contracts\CategoryRepositoryContract;
use App\Repositories\Contracts\CommentRepositoryContract;
use App\Repositories\Contracts\DatabaseHealthRepositoryContract;
use App\Repositories\Contracts\FeedRepositoryContract;
use App\Repositories\Contracts\PostViewRepositoryContract;
use App\Repositories\Contracts\ReactionRepositoryContract;
use App\Repositories\Contracts\SearchRepositoryContract;
use App\Repositories\Contracts\SiteHealthReportRepositoryContract;
use App\Repositories\Contracts\SiteOperationalRepositoryContract;
use App\Repositories\Contracts\TagRepositoryContract;
use App\Repositories\DatabaseHealthRepository;
use App\Repositories\FeedRepository;
use App\Repositories\PostViewRepository;
use App\Repositories\ReactionRepository;
use App\Repositories\SearchRepository;
use App\Repositories\SiteHealthReportRepository;
use App\Repositories\SiteOperationalRepository;
use App\Repositories\TagRepository;
use App\Services\AuthorService;
use App\Services\CommentService;
use App\Services\Contracts\AuthorServiceContract;
use App\Services\Contracts\CommentServiceContract;
use App\Services\Contracts\FeedServiceContract;
use App\Services\Contracts\HealthCheckServiceContract;
use App\Services\Contracts\ModerationDashboardServiceContract;
use App\Services\Contracts\PostViewServiceContract;
use App\Services\Contracts\ReactionServiceContract;
use App\Services\Contracts\ScheduledPublishServiceContract;
use App\Services\Contracts\SearchPageServiceContract;
use App\Services\Contracts\SearchServiceContract;
use App\Services\Contracts\SiteHealthServiceContract;
use App\Services\Contracts\SiteOperationalServiceContract;
use App\Services\FeedService;
use App\Services\Health\HealthCheckService;
use App\Services\ModerationDashboardService;
use App\Services\PostViewService;
use App\Services\ReactionService;
use App\Services\ScheduledPublishService;
use App\Services\SearchPageService;
use App\Services\SearchService;
use App\Services\SiteHealthService;
use App\Services\SiteOperationalService;
use App\Support\SiteHealth\Checks\AppKeyCheck;
use App\Support\SiteHealth\Checks\DatabaseCheck;
use App\Support\SiteHealth\Checks\LaravelVersionCheck;
use App\Support\SiteHealth\Checks\MigrationsUpToDateCheck;
use App\Support\SiteHealth\Checks\NginxCheck;
use App\Support\SiteHealth\Checks\OwnerExistsCheck;
use App\Support\SiteHealth\Checks\PhpVersionCheck;
use App\Support\SiteHealth\Checks\QueueWorkerHeartbeatCheck;
use App\Support\SiteHealth\Checks\RedisCheck;
use App\Support\SiteHealth\Checks\SecurityHeadersCheck;
use App\Support\SiteHealth\Checks\StorageWritableCheck;
use Illuminate\Queue\Events\Looping;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider site health service provider.
 */
class SiteHealthServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует сервисы контейнера.
     */
    public function register(): void
    {
        $this->app->bind(SiteHealthReportRepositoryContract::class, SiteHealthReportRepository::class);
        $this->app->bind(SiteOperationalRepositoryContract::class, SiteOperationalRepository::class);
        $this->app->bind(SiteOperationalServiceContract::class, SiteOperationalService::class);
        $this->app->bind(HealthCheckServiceContract::class, HealthCheckService::class);
        $this->app->bind(CategoryRepositoryContract::class, CategoryRepository::class);
        $this->app->bind(TagRepositoryContract::class, TagRepository::class);
        $this->app->bind(SearchRepositoryContract::class, SearchRepository::class);
        $this->app->bind(CommentRepositoryContract::class, CommentRepository::class);
        $this->app->bind(ReactionRepositoryContract::class, ReactionRepository::class);
        $this->app->bind(PostViewRepositoryContract::class, PostViewRepository::class);
        $this->app->bind(FeedRepositoryContract::class, FeedRepository::class);
        $this->app->bind(AuthorRepositoryContract::class, AuthorRepository::class);
        $this->app->bind(SearchServiceContract::class, SearchService::class);
        $this->app->bind(SearchPageServiceContract::class, SearchPageService::class);
        $this->app->bind(CommentServiceContract::class, CommentService::class);
        $this->app->bind(ReactionServiceContract::class, ReactionService::class);
        $this->app->bind(PostViewServiceContract::class, PostViewService::class);
        $this->app->bind(FeedServiceContract::class, FeedService::class);
        $this->app->bind(AuthorServiceContract::class, AuthorService::class);
        $this->app->bind(ScheduledPublishServiceContract::class, ScheduledPublishService::class);
        $this->app->bind(DatabaseHealthRepositoryContract::class, DatabaseHealthRepository::class);
        $this->app->bind(ModerationDashboardServiceContract::class, ModerationDashboardService::class);
        $this->app->tag([PhpVersionCheck::class, LaravelVersionCheck::class, DatabaseCheck::class, RedisCheck::class,
            NginxCheck::class, SecurityHeadersCheck::class, AppKeyCheck::class, MigrationsUpToDateCheck::class,
            OwnerExistsCheck::class, StorageWritableCheck::class, QueueWorkerHeartbeatCheck::class],
            'site-health.checks');
        $this->app->bind(SiteHealthServiceContract::class, function ($app): SiteHealthService {
            return new SiteHealthService($app->make(SiteHealthReportRepositoryContract::class),
                $app->tagged('site-health.checks'));
        });
    }

    /**
     * Регистрирует зависимости при загрузке.
     */
    public function boot(): void
    {
        Event::listen(Looping::class, RecordQueueWorkerHeartbeatListener::class);
    }
}
