<?php

namespace App\Enums;

/**
 * Статус платёжного intent до подтверждения webhook.
 */
enum PaymentIntentStatus: string
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
}
