<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP middleware assign request id.
 */
final class AssignRequestId
{
    public const ATTRIBUTE = 'request_id';

    public const HEADER = 'X-Request-Id';

    /**
     * Обрабатывает запрос или задачу.

     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->headers->get(self::HEADER);
        if (! is_string($requestId) || $requestId === '') {
            $requestId = (string) Str::uuid();
        }
        $request->attributes->set(self::ATTRIBUTE, $requestId);
        Log::shareContext(['request_id' => $requestId]);
        /** @var Response $response */
        $response = $next($request);
        $response->headers->set(self::HEADER, $requestId);

        return $response;
    }
}
