<?php

$allowedIps = array_values(array_filter(array_map(
    static fn (string $ip): string => trim($ip),
    explode(',', (string) env('HEALTH_ALLOWED_IPS', '')),
), static fn (string $ip): bool => $ip !== ''));

$exposeProbeDetails = env('HEALTH_EXPOSE_PROBE_DETAILS');

return [
    /*
    |--------------------------------------------------------------------------
    | Health endpoint access
    |--------------------------------------------------------------------------
    |
    | /up — публичный liveness (Laravel). /health — readiness с опциональной
    | детализацией: в production по умолчанию только status (HTTP 200/503).
    |
    | HEALTH_ALLOWED_IPS — CSV (поддержка CIDR). Пусто = доступ без IP-фильтра.
    | Если список задан — остальные IP получают 403.
    |
    | HEALTH_EXPOSE_PROBE_DETAILS — true/false; если не задано: полный JSON
    | вне production, в production — только для IP из allowlist.
    |
    */
    'allowed_ips' => $allowedIps,

    'expose_probe_details' => $exposeProbeDetails === null
        ? null
        : filter_var($exposeProbeDetails, FILTER_VALIDATE_BOOLEAN),
];
