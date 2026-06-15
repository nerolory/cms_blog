<?php

namespace App\Enums;

/**
 * Перечисление service criticality.
 */
enum ServiceCriticality: string
{
    case Critical = 'critical';
    case Optional = 'optional';

    /**
     * for.

     *
     * @return self
     */
    public static function forService(OperationalService $service): self
    {
        /** @var array<string, string> $map */
        $map = config('site_operational.criticality', []);
        $value = $map[$service->value] ?? self::Critical->value;

        return self::tryFrom($value) ?? self::Critical;
    }
}
