<?php

use App\Support\Theme;

if (! function_exists('theme_layout')) {
    function theme_layout(string $name = 'app'): string
    {
        return Theme::layout($name);
    }
}

if (! function_exists('csp_nonce')) {
    /**
     * Возвращает CSP nonce текущего запроса (для script/style в Blade).
     */
    function csp_nonce(): string
    {
        $nonce = request()->attributes->get('csp_nonce');

        return is_string($nonce) ? $nonce : '';
    }
}

if (! function_exists('csp_nonce_attribute')) {
    /**
     * HTML-атрибут nonce для inline script/style (пустая строка, если CSP выключен).
     */
    function csp_nonce_attribute(): string
    {
        $nonce = csp_nonce();

        return $nonce !== '' ? 'nonce="'.e($nonce).'"' : '';
    }
}
