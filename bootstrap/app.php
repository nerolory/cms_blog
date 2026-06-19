<?php

use App\Http\Middleware\ApplyAudienceCacheHeaders;
use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\ConditionalGet;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureSiteOperational;
use App\Http\Middleware\PrepareEngagementSpaRequest;
use App\Http\Middleware\PreventPreviewCaching;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SyncBrowserCacheVersion;
use App\Jobs\PersistPostViewCountsJob;
use App\Services\Contracts\SiteOperationalServiceContract;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('posts:cleanup-previews')->hourly();
        $schedule->command('posts:publish-scheduled')->everyMinute();
        $schedule->job(new PersistPostViewCountsJob)->everyMinute();
        $schedule->call(static function (): void {
            app(SiteOperationalServiceContract::class)->assess();
        })->everyThirtySeconds();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
        $middleware->append(AssignRequestId::class);

        $middleware->web(prepend: [
            PrepareEngagementSpaRequest::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
            SyncBrowserCacheVersion::class,
            EnsureSiteOperational::class,
            ApplyAudienceCacheHeaders::class,
        ]);

        $middleware->throttleApi('api');

        $middleware->alias([
            'account.active' => EnsureAccountIsActive::class,
            'preview.no-cache' => PreventPreviewCaching::class,
            'conditional.get' => ConditionalGet::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*')
                || $request->header('X-Engagement-Spa') === '1',
        );

        $exceptions->render(function (ThrottleRequestsException $exception, Request $request) {
            if ($request->routeIs('posts.update')) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['form_error' => __('posts.messages.too_many_updates')]);
            }

            return null;
        });
    })->create();
