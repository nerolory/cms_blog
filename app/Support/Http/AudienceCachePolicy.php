<?php

namespace App\Support\Http;

use App\Support\TypeCast;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Политика HTTP-кэша публичного сайта с персональной шапкой.
 *
 * Разделение аудиторий — через Vary: Cookie (сессия Laravel в Cookie).
 * Гость и каждый авторизованный пользователь получают свой слот
 * в кэше браузера.
 */
final class AudienceCachePolicy
{
    public const VARY_HEADER = 'Cookie';

    /**
     * Применять ли политику к ответу.
     *
     * @return bool
     */
    public static function applies(Request $request, Response $response): bool
    {
        if (! $response->isSuccessful() || $response->isRedirection()) {
            return false;
        }

        if ($request->is(
            'admin',
            'admin/*',
            'livewire/*',
            'filament/*',
            'api/*',
            'up',
            'health',
        )) {
            return false;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));

        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return false;
        }

        $cacheControl = strtolower((string) $response->headers->get('Cache-Control', ''));

        return ! str_contains($cacheControl, 'no-store');
    }

    /**
     * Cache-Control по умолчанию для HTML с персонализацией.
     *
     * @return string
     */
    public static function defaultCacheControl(): string
    {
        $maxAge = TypeCast::int(config('seo.cache.max_age'), 3600);

        return sprintf('private, max-age=%d, must-revalidate', $maxAge);
    }

    /**
     * Добавляет Vary: Cookie, если его ещё нет.
     */
    public static function applyVary(Response $response): void
    {
        $existing = $response->headers->get('Vary');

        if ($existing === null || $existing === '') {
            $response->headers->set('Vary', self::VARY_HEADER);

            return;
        }

        $parts = array_map('trim', explode(',', $existing));
        foreach ($parts as $part) {
            if (strcasecmp($part, self::VARY_HEADER) === 0) {
                return;
            }
        }

        $response->headers->set('Vary', $existing.', '.self::VARY_HEADER);
    }

    /**
     * Базовые заголовки кэша, если маршрут не задал свои (conditional.get).
     */
    public static function applyDefaults(Response $response): void
    {
        if (! $response->headers->has('Cache-Control') || self::shouldReplaceCacheControl($response)) {
            $response->headers->set('Cache-Control', self::defaultCacheControl());
        }

        self::applyVary($response);
    }

    /**
     * Заменяет дефолт Symfony/Laravel (no-cache, private) без max-age.
     */
    private static function shouldReplaceCacheControl(Response $response): bool
    {
        $cacheControl = strtolower((string) $response->headers->get('Cache-Control', ''));

        return ! str_contains($cacheControl, 'max-age=');
    }
}
