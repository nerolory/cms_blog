<?php

namespace App\Http\Middleware;

use App\Support\Http\AudienceCachePolicy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Единая политика кэша HTML: private + Vary: Cookie на всём публичном сайте.
 */
class ApplyAudienceCacheHeaders
{
    /**
     * Разделяет кэш гостя и auth-сессий на публичном сайте.
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! AudienceCachePolicy::applies($request, $response)) {
            return $response;
        }

        AudienceCachePolicy::applyDefaults($response);

        return $response;
    }
}
