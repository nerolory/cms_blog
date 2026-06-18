<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Engagement SPA: JSON-семантика для auth, CSRF и исключений при X-Engagement-Spa.
 */
class PrepareEngagementSpaRequest
{
    /**
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('X-Engagement-Spa') === '1' && ! $request->wantsJson()) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
