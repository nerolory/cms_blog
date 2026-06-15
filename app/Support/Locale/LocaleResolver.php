<?php

namespace App\Support\Locale;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Вспомогательный класс locale resolver.
 */
final class LocaleResolver
{
    public const COOKIE = 'preferred_locale';

    public const SUPPORTED = ['ru', 'en'];

    /**
     * resolve.
     *
     * @param  Request  $request  HTTP-запрос

     * @return string
     */
    public function resolve(Request $request): string
    {
        $sessionLocale = $request->session()->get('locale');
        if (is_string($sessionLocale) && $this->isSupported($sessionLocale)) {
            return $sessionLocale;
        }
        $cookieLocale = $request->cookie(self::COOKIE);
        if (is_string($cookieLocale) && $this->isSupported($cookieLocale)) {
            return $cookieLocale;
        }
        $user = $request->user();
        if ($user instanceof User && is_string($user->locale) && $this->isSupported($user->locale)) {
            return $user->locale;
        }
        $appLocale = config('app.locale', 'ru');

        return $this->fromAcceptLanguage($request) ?? (is_string($appLocale) ? $appLocale : 'ru');
    }

    /**
     * from accept language.
     *
     * @param  Request  $request  HTTP-запрос

     * @return ?string
     */
    public function fromAcceptLanguage(Request $request): ?string
    {
        $preferred = $request->getPreferredLanguage(self::SUPPORTED);
        if ($preferred === 'ru') {
            return 'ru';
        }
        if ($preferred !== null) {
            return 'en';
        }
        $header = $request->header('Accept-Language', '');
        if ($header === '') {
            return null;
        }

        return str_starts_with(strtolower($header), 'ru') ? 'ru' : 'en';
    }

    /**
     * Проверяет supported.

     *
     * @return bool
     */
    public function isSupported(string $locale): bool
    {
        return in_array($locale, self::SUPPORTED, true);
    }
}
