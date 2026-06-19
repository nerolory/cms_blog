<?php

namespace App\Providers;

use App\Events\PostPublished;
use App\Events\PostSubmitted;
use App\Events\PostUpdated;
use App\Listeners\InvalidatePublicListingCacheListener;
use App\Listeners\QueuePostSearchIndexListener;
use App\Listeners\RecordPostPublishedListener;
use App\Listeners\RecordPostSubmittedListener;
use App\Listeners\RecordPostUpdatedListener;
use App\Presenters\PostPresenterFactory;
use App\Presenters\UserPresenterFactory;
use App\Repositories\Contracts\FileRepositoryContract;
use App\Repositories\Contracts\MailSettingsRepositoryContract;
use App\Repositories\Contracts\PermissionRepositoryContract;
use App\Repositories\Contracts\PostModerationLogRepositoryContract;
use App\Repositories\Contracts\PostPreviewCacheRepositoryContract;
use App\Repositories\Contracts\PostRepositoryContract;
use App\Repositories\Contracts\PostVersionRepositoryContract;
use App\Repositories\Contracts\RoleRepositoryContract;
use App\Repositories\Contracts\SeoRepositoryContract;
use App\Repositories\Contracts\SiteSettingsRepositoryContract;
use App\Repositories\Contracts\SiteTemplateRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Repositories\FileRepository;
use App\Repositories\MailSettingsRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\PostModerationLogRepository;
use App\Repositories\PostPreviewCacheRepository;
use App\Repositories\PostRepository;
use App\Repositories\PostVersionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\SeoRepository;
use App\Repositories\SiteSettingsRepository;
use App\Repositories\SiteTemplateRepository;
use App\Repositories\UserRepository;
use App\Services\Contracts\LocaleServiceContract;
use App\Services\Contracts\MailSettingsServiceContract;
use App\Services\Contracts\PostContentImageServiceContract;
use App\Services\Contracts\PostPreviewServiceContract;
use App\Services\Contracts\PostServiceContract;
use App\Services\Contracts\PostShowPageServiceContract;
use App\Services\Contracts\PostVersionServiceContract;
use App\Services\Contracts\SeoServiceContract;
use App\Services\Contracts\SiteSettingsServiceContract;
use App\Services\Contracts\SiteTemplateServiceContract;
use App\Services\Contracts\StoredImageOptimizationServiceContract;
use App\Services\Contracts\UserServiceContract;
use App\Services\LocaleService;
use App\Services\MailSettingsService;
use App\Services\Post\PostMutationPipeline;
use App\Services\PostContentImageService;
use App\Services\PostPreviewService;
use App\Services\PostService;
use App\Services\PostShowPageService;
use App\Services\PostVersionService;
use App\Services\SeoService;
use App\Services\SiteSettingsService;
use App\Services\SiteTemplateService;
use App\Services\StoredImageOptimizationService;
use App\Services\UserService;
use App\Support\Config\ProductionConfigGuard;
use App\Support\Post\VisibilityChecker;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider app service provider.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(VisibilityChecker::class);
        $this->app->bind(StoredImageOptimizationServiceContract::class, StoredImageOptimizationService::class);
        $this->app->singleton(PostMutationPipeline::class);
        $this->app->singleton(PostPresenterFactory::class);
        $this->app->singleton(UserPresenterFactory::class);
        $this->app->bind(PostRepositoryContract::class, PostRepository::class);
        $this->app->bind(PostVersionRepositoryContract::class, PostVersionRepository::class);
        $this->app->bind(PostVersionServiceContract::class, PostVersionService::class);
        $this->app->bind(PostPreviewCacheRepositoryContract::class, PostPreviewCacheRepository::class);
        $this->app->bind(PostPreviewServiceContract::class, PostPreviewService::class);
        $this->app->bind(PostModerationLogRepositoryContract::class, PostModerationLogRepository::class);
        $this->app->bind(PostServiceContract::class, PostService::class);
        $this->app->bind(PostShowPageServiceContract::class, PostShowPageService::class);
        $this->app->bind(SeoRepositoryContract::class, SeoRepository::class);
        $this->app->bind(SeoServiceContract::class, SeoService::class);
        $this->app->bind(UserRepositoryContract::class, UserRepository::class);
        $this->app->bind(RoleRepositoryContract::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryContract::class, PermissionRepository::class);
        $this->app->bind(FileRepositoryContract::class, FileRepository::class);
        $this->app->bind(LocaleServiceContract::class, LocaleService::class);
        $this->app->bind(PostContentImageServiceContract::class, PostContentImageService::class);
        $this->app->bind(UserServiceContract::class, UserService::class);
        $this->app->bind(MailSettingsRepositoryContract::class, MailSettingsRepository::class);
        $this->app->singleton(MailSettingsServiceContract::class, MailSettingsService::class);
        $this->app->bind(SiteSettingsRepositoryContract::class, SiteSettingsRepository::class);
        $this->app->singleton(SiteSettingsServiceContract::class, SiteSettingsService::class);
        $this->app->singleton(SiteTemplateRepositoryContract::class, SiteTemplateRepository::class);
        $this->app->singleton(SiteTemplateServiceContract::class, SiteTemplateService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ProductionConfigGuard::assertBootRequirements();
        $this->configureRateLimiting();
        $this->configureBladeCsrf();
        Paginator::useBootstrapFive();
        Event::listen(PostSubmitted::class, RecordPostSubmittedListener::class);
        Event::listen(PostUpdated::class, RecordPostUpdatedListener::class);
        Event::listen(PostPublished::class, RecordPostPublishedListener::class);
        Event::listen([PostPublished::class, PostUpdated::class], QueuePostSearchIndexListener::class);
        Event::listen([PostPublished::class, PostUpdated::class],
            InvalidatePublicListingCacheListener::class);
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('api-write', function (Request $request): Limit {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * @csrf без autocomplete="off" — иначе W3C Nu Validator ругается на hidden input.
     */
    private function configureBladeCsrf(): void
    {
        Blade::precompiler(function (string $value): string {
            return preg_replace(
                '/@csrf\b/',
                '<?php echo \\App\\Support\\Html\\CsrfField::render(); ?>',
                $value,
            ) ?? $value;
        });
    }
}
