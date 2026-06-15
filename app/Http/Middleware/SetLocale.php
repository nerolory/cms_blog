<?php

namespace App\Http\Middleware;

use App\Support\Locale\LocaleResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP middleware set locale.
 *
 * @property-read LocaleResolver $localeResolver
 */
class SetLocale
{
    public function __construct(protected LocaleResolver $localeResolver) {}

    /**
     * Обрабатывает запрос или задачу.

     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($this->localeResolver->resolve($request));

        return $next($request);
    }
}
