<?php

namespace App\Http\Middleware;

use App\Support\Cache\CacheVersionManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exposes the current cache generation for debugging and optional client-side asset URLs.
 * Invalidation is server-side (cache keys + ?v= on media URLs), not via page reload.
 *
 * @property-read CacheVersionManager $cacheVersions
 */
class SyncBrowserCacheVersion
{
    public function __construct(protected CacheVersionManager $cacheVersions) {}

    /**
     * Обрабатывает запрос или задачу.

     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-App-Cache-Version', $this->cacheVersions->current());

        return $response;
    }
}
