<?php

namespace App\Enums;

use App\Support\TypeCast;

/**
 * Перечисление site health context.
 */
enum SiteHealthContext: string
{
    case Docker = 'docker';
    case Native = 'native';
    case K8s = 'k8s';

    /**
     * from config.

     *
     * @return self
     */
    public static function fromConfig(): self
    {
        return self::tryFrom(TypeCast::string(config('site_health.context', 'native'))) ?? self::Native;
    }
}
