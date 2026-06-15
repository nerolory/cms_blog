<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Исключение домена site template.
 */
class SiteTemplateException extends RuntimeException
{
    /**
     * Проверяет возможность not delete default.

     *
     * @return self
     */
    public static function cannotDeleteDefault(): self
    {
        return new self('Нельзя удалить шаблон по умолчанию.');
    }

    /**
     * Проверяет возможность not delete active.

     *
     * @return self
     */
    public static function cannotDeleteActive(): self
    {
        return new self('Нельзя удалить активный шаблон.');
    }

    /**
     * Проверяет возможность not delete default theme.

     *
     * @return self
     */
    public static function cannotDeleteDefaultTheme(): self
    {
        return new self('Нельзя удалить тему по умолчанию.');
    }
}
