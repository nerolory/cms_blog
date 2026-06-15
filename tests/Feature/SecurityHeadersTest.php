<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Класс security headers.
 */
class SecurityHeadersTest extends TestCase
{
    /**
     * Security headers middleware adds standard protection headers.
     */
    public function test_responses_include_security_headers(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('X-XSS-Protection', '0');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    /**
     * CSP uses nonce and does not allow unsafe-inline.
     */
    public function test_csp_uses_nonce_without_unsafe_inline(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertNotSame('', $csp);
        $this->assertStringNotContainsString('unsafe-inline', $csp);
        $this->assertMatchesRegularExpression("/script-src[^;]*'nonce-[^']+'/", $csp);
        $this->assertMatchesRegularExpression("/style-src[^;]*'nonce-[^']+'/", $csp);
    }

    /**
     * HSTS is omitted outside production.
     */
    public function test_hsts_is_not_sent_in_testing_environment(): void
    {
        $response = $this->get('/');
        $this->assertFalse($response->headers->has('Strict-Transport-Security'));
    }
}
