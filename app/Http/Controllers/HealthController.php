<?php

namespace App\Http\Controllers;

use App\DTO\HealthProbeResult;
use App\Services\Contracts\HealthCheckServiceContract;
use App\Support\Health\HealthEndpointAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * HTTP-контроллер health.
 */
final class HealthController extends Controller
{
    /**
     * Readiness-check: в production — урезанный JSON; детали probes — по политике.

     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, HealthCheckServiceContract $health): JsonResponse
    {
        $status = $health->assess();
        $criticalOk = $health->isCriticalOk($status);
        $optionalDegraded = $health->isOptionalDegraded($status);
        $httpStatus = $criticalOk ? 200 : 503;
        $aggregateStatus = $criticalOk && ! $optionalDegraded ? 'ok' : 'degraded';

        if (! HealthEndpointAccess::exposesProbeDetails($request)) {
            return response()->json(['status' => $aggregateStatus], $httpStatus);
        }

        $checks = $health->run($status);

        return response()->json([
            'status' => $aggregateStatus,
            'critical_ok' => $criticalOk,
            'optional_degraded' => $optionalDegraded,
            'checks' => $checks->map(
                static fn (HealthProbeResult $result): array => $result->toArray(),
            )->values()->all(),
        ], $httpStatus);
    }
}
