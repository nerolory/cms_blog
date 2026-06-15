<?php

namespace App\Enums;

/**
 * Перечисление token transaction type.
 */
enum TokenTransactionType: string
{
    case Purchase = 'purchase';
    case Spend = 'spend';
    case Refund = 'refund';
    case AdminGrant = 'admin_grant';

    /**
     * label.

     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Purchase => __('tokens.transactions.purchase'),
            self::Spend => __('tokens.transactions.spend'),
            self::Refund => __('tokens.transactions.refund'),
            self::AdminGrant => __('tokens.transactions.admin_grant'),
        };
    }
}
