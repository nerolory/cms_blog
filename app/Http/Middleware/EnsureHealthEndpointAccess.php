<?php

namespace App\Http\Middleware;

use App\Support\Health\HealthEndpointAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ограничивает /health по IP, если задан HEALTH_ALLOWED_IPS.
 */
class EnsureHealthEndpointAccess
{
    /**
     * Блокирует запрос, если IP не входит в allowlist.
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! HealthEndpointAccess::allowsRequest($request)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
