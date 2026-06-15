<?php

namespace App\Enums;

use App\Support\TypeCast;

/**
 * Перечисление site health profile.
 */
enum SiteHealthProfile: string
{
    case Default = 'default';
    case InstallCheck = 'install-check';

    /**
     * from string.

     *
     * @return self
     */
    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? self::Default;
    }

    /**
     * check names.
     *
     * @return list<string>
     */
    public function checkNames(): array
    {
        /** @var list<string> $names */
        $names = config('site_health.profiles.'.$this->value, []);

        return $names;
    }

    /**
     * from.

     *
     * @return self
     */
    public static function fromRequest(?string $profile): self
    {
        if ($profile === null || $profile === '') {
            return self::Default;
        }

        return self::fromString(TypeCast::string($profile));
    }
}
