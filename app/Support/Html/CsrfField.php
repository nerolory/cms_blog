<?php

namespace App\Support\Html;

use Illuminate\Support\HtmlString;

/**
 * CSRF hidden input без autocomplete (W3C Nu Validator).
 */
final class CsrfField
{
    /**
     * Рендерит hidden _token для форм.
     *
     * @return HtmlString
     */
    public static function render(): HtmlString
    {
        return new HtmlString('<input type="hidden" name="_token" value="'.e(csrf_token()).'">');
    }
}
