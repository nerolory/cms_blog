<?php

namespace App\Exceptions;

use Carbon\CarbonInterface;
use RuntimeException;

/**
 * Исключение домена ai analysis order.
 */
class AiAnalysisOrderException extends RuntimeException
{
    /**
     * pending order exists.

     *
     * @return self
     */
    public static function pendingOrderExists(): self
    {
        return new self(__('ai.orders.errors.pending_exists'));
    }

    /**
     * cooldown active.

     *
     * @return self
     */
    public static function cooldownActive(CarbonInterface $until): self
    {
        return new self(__('ai.orders.errors.cooldown_active', ['until' => $until->translatedFormat('d.m.Y H:i')]));
    }

    /**
     * invalid status transition.

     *
     * @return self
     */
    public static function invalidStatusTransition(): self
    {
        return new self(__('ai.orders.errors.invalid_status'));
    }

    /**
     * insufficient comments for auto.

     *
     * @return self
     */
    public static function insufficientCommentsForAuto(int $threshold): self
    {
        return new self(__('ai.orders.errors.auto_threshold', ['count' => $threshold]));
    }
}
