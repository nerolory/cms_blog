<?php

namespace App\Http\Middleware;

use App\Support\TypeCast;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies standard HTTP security headers to every response.
 */
class SecurityHeaders
{
    /**
     * Обрабатывает запрос или задачу.
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = null;
        if (config('security.csp.enabled', false)) {
            $nonce = base64_encode(random_bytes(16));
            Vite::useCspNonce($nonce);
            $request->attributes->set('csp_nonce', $nonce);
        }

        $response = $next($request);
        $response->headers->set('X-Frame-Options', TypeCast::string(config('security.frame_options', 'SAMEORIGIN')));
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', TypeCast::string(config('security.referrer_policy',
            'strict-origin-when-cross-origin')));
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Permissions-Policy', TypeCast::string(config('security.permissions_policy',
            'camera=(), microphone=(), geolocation=()')));
        if (config('security.hsts.enabled', false)) {
            $maxAge = TypeCast::int(config('security.hsts.max_age', 31536000));
            $directive = "max-age={$maxAge}";
            if (config('security.hsts.include_subdomains', true)) {
                $directive .= '; includeSubDomains';
            }
            $response->headers->set('Strict-Transport-Security', $directive);
        }
        if (config('security.csp.enabled', false) && is_string($nonce)) {
            $response->headers->set('Content-Security-Policy', $this->buildCspDirectives($nonce));
        }

        return $response;
    }

    private function buildCspDirectives(string $nonce): string
    {
        $nonceDirective = "'nonce-{$nonce}'";

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' {$nonceDirective} https://cdn.jsdelivr.net",
            "style-src 'self' {$nonceDirective} https://cdn.jsdelivr.net",
            "img-src 'self' data: blob:",
            "font-src 'self' data:",
            "connect-src 'self'",
            "frame-ancestors 'self'",
        ]);
    }
}
