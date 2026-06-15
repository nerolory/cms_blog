<?php

namespace App\Support\Health;

use App\Support\TypeCast;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Политика доступа к /health: IP-allowlist и уровень детализации ответа.
 */
final class HealthEndpointAccess
{
    /**
     * Разрешён ли запрос к /health с текущего IP.
     *
     * @return bool
     */
    public static function allowsRequest(Request $request): bool
    {
        /** @var list<string> $allowedIps */
        $allowedIps = TypeCast::array(config('health.allowed_ips', []));
        if ($allowedIps === []) {
            return true;
        }

        return IpUtils::checkIp((string) $request->ip(), $allowedIps);
    }

    /**
     * Включать ли в ответ массив probes и диагностические поля.
     *
     * @return bool
     */
    public static function exposesProbeDetails(Request $request): bool
    {
        $explicit = config('health.expose_probe_details');
        if ($explicit !== null) {
            return (bool) $explicit;
        }

        if (! app()->environment('production')) {
            return true;
        }

        /** @var list<string> $allowedIps */
        $allowedIps = TypeCast::array(config('health.allowed_ips', []));
        if ($allowedIps === []) {
            return false;
        }

        return self::allowsRequest($request);
    }
}
