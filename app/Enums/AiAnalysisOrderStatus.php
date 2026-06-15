<?php

namespace App\Enums;

/**
 * Перечисление ai analysis order status.
 */
enum AiAnalysisOrderStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Executing = 'executing';
    case Completed = 'completed';
    case Failed = 'failed';

    /**
     * Проверяет terminal.

     *
     * @return bool
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::Completed, self::Rejected, self::Failed => true,
            default => false,
        };
    }
}
