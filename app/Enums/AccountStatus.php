<?php

namespace App\Enums;

/**
 * Перечисление account status.
 */
enum AccountStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';
}
