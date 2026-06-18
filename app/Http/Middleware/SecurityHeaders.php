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
            if ($response->getStatusCode() !== Response::HTTP_NOT_MODIFIED
                && $response->getStatusCode() < Response::HTTP_INTERNAL_SERVER_ERROR) {
                $response->headers->set(
                    'Content-Security-Policy',
                    $this->buildCspDirectives($nonce, $this->isPanelRequest($request)),
                );
            }
        }

        return $response;
    }

    /**
     * Filament/Livewire/Alpine: отдельная политика с unsafe-eval (требование Alpine.js).
     */
    private function isPanelRequest(Request $request): bool
    {
        return $request->is('admin', 'admin/*', 'livewire/*', 'filament/*');
    }

    private function buildCspDirectives(string $nonce, bool $isPanel): string
    {
        $nonceDirective = "'nonce-{$nonce}'";
        $scriptSrc = implode(' ', array_filter([
            "'self'",
            $nonceDirective,
            'https://cdn.jsdelivr.net',
            $isPanel ? "'unsafe-eval'" : null,
        ]));
        $connectSrc = "'self'".($isPanel ? ' ws: wss:' : '');

        return implode('; ', [
            "default-src 'self'",
            "script-src {$scriptSrc}",
            "style-src 'self' {$nonceDirective} https://cdn.jsdelivr.net",
            "style-src-attr 'unsafe-inline'",
            "img-src 'self' data: blob:",
            "font-src 'self' data:",
            "connect-src {$connectSrc}",
            "frame-ancestors 'self'",
        ]);
    }
}
