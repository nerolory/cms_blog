<?php

namespace App\Enums;

/**
 * Перечисление post visibility.
 */
enum PostVisibility: string
{
    case Guest = 'guest';
    case Authenticated = 'authenticated';
    case Admin = 'admin';
    case Permission = 'permission';

    /**
     * Whether visibility requires a permission FK.

     *
     * @return bool
     */
    public function requiresPermission(): bool
    {
        return $this === self::Permission;
    }

    /**
     * Visibility values authors may set on the public web form.
     *
     * @return list<string>
     */
    public static function authorWebValues(): array
    {
        return [self::Guest->value, self::Authenticated->value];
    }
}
