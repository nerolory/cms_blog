<?php

namespace App\Http\Middleware;

use App\Services\Contracts\SiteOperationalServiceContract;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP middleware ensure site operational.

 *
 * @property-read SiteOperationalServiceContract $siteOperational
 */
class EnsureSiteOperational
{
    public function __construct(protected SiteOperationalServiceContract $siteOperational) {}

    /**
     * Показывает страницу обслуживания гостям и модераторам при
     * сбое критичных сервисов.

     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }
        $criticalOk = $this->siteOperational->getCachedCriticalOk();
        if ($criticalOk === null) {
            $criticalOk = $this->siteOperational->isCriticalOperational();
        }
        if ($criticalOk) {
            return $next($request);
        }
        if ($this->siteOperational->canBypassMaintenance($request) || $this->isFilamentLivewireRequest($request)) {
            return $next($request);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => __('maintenance.message')], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return response()->view('pages.maintenance', [], Response::HTTP_SERVICE_UNAVAILABLE);
    }

    private function shouldSkip(Request $request): bool
    {
        return $request->routeIs('health') || $request->is('up') || $request->is('health');
    }

    /**
     * Livewire POST идёт на /livewire/*, не под /admin — пропускаем для owner/admin с
     * Filament.
     */
    private function isFilamentLivewireRequest(Request $request): bool
    {
        if (! $request->is('livewire/*')) {
            return false;
        }

        $user = $request->user();
        if ($user === null || ! ($user->hasRole('owner') || $user->hasRole('admin'))) {
            return false;
        }

        $referer = (string) $request->headers->get('referer', '');

        return str_contains($referer, '/admin');
    }
}
