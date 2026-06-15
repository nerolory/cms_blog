<?php

namespace App\Enums;

/**
 * Перечисление ai result status.
 */
enum AiResultStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Expired = 'expired';

    /**
     * Проверяет readable.

     *
     * @return bool
     */
    public function isReadable(): bool
    {
        return $this === self::Completed;
    }
}
