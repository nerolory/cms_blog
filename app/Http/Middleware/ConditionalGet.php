<?php

namespace App\Http\Middleware;

use App\DTO\HttpCacheContext;
use App\Support\Http\AudienceCachePolicy;
use App\Support\Http\HttpCacheRequestAttributes;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Conditional GET (ETag, Last-Modified) для маршрутов с HttpCacheContext.
 * ETag включает id зрителя — кэш гостя и каждого пользователя разделён.
 */
class ConditionalGet
{
    /**
     * Обрабатывает запрос или задачу.

     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $context = $request->attributes->get(HttpCacheRequestAttributes::CACHE_CONTEXT);
        if (! $context instanceof HttpCacheContext || ! $response->isSuccessful()) {
            return $response;
        }
        if ($this->isNotModified($request, $context)) {
            $notModified = response('', Response::HTTP_NOT_MODIFIED);
            $this->applyCacheHeaders($notModified, $context);

            return $notModified;
        }
        $this->applyCacheHeaders($response, $context);

        return $response;
    }

    private function applyCacheHeaders(Response $response, HttpCacheContext $context): void
    {
        $response->headers->set('Cache-Control', $context->cacheControl);
        AudienceCachePolicy::applyVary($response);
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s',
            $context->lastModified->getTimestamp()).' GMT');
        $response->headers->set('ETag', $context->etag);
        if ($context->robotsTag !== null && $context->robotsTag !== '') {
            $response->headers->set('X-Robots-Tag', $context->robotsTag);
        }
    }

    private function isNotModified(Request $request, HttpCacheContext $context): bool
    {
        $ifNoneMatch = $request->headers->get('If-None-Match');
        if ($ifNoneMatch !== null) {
            $tags = array_map('trim', explode(',', $ifNoneMatch));
            if (in_array($context->etag, $tags, true) || in_array('*', $tags, true)) {
                return true;
            }
        }
        $ifModifiedSince = $request->headers->get('If-Modified-Since');
        if ($ifModifiedSince === null) {
            return false;
        }
        $since = strtotime($ifModifiedSince);
        if ($since === false) {
            return false;
        }

        return $context->lastModified->getTimestamp() <= $since;
    }
}
