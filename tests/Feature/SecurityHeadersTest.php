<?php

namespace Tests\Feature;

use App\Http\Middleware\SecurityHeaders;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс security headers.
 */
class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    /**
     * Подготавливает окружение теста.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        Cache::flush();
    }

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
        $this->assertMatchesRegularExpression("/script-src[^;]*'nonce-[^']+'/", $csp);
        $this->assertMatchesRegularExpression("/style-src[^;]*'nonce-[^']+'/", $csp);
        foreach (explode(';', $csp) as $directive) {
            $directive = trim($directive);
            if (str_starts_with($directive, 'style-src ')) {
                $this->assertStringNotContainsString('unsafe-inline', $directive);
            }
            if (str_starts_with($directive, 'script-src ')) {
                $this->assertStringNotContainsString('unsafe-inline', $directive);
            }
        }
        $this->assertStringContainsString("style-src-attr 'unsafe-inline'", $csp);
    }

    /**
     * 304 не должен отдавать новый CSP-nonce: тело берётся из кеша
     * браузера.
     */
    public function test_csp_is_omitted_on_not_modified_response(): void
    {
        $first = $this->get('/posts');
        $first->assertOk();
        $etag = (string) $first->headers->get('ETag');
        $this->assertNotSame('', $etag);
        $this->assertTrue($first->headers->has('Content-Security-Policy'));

        $second = $this->withHeaders(['If-None-Match' => $etag])->get('/posts');
        $second->assertNotModified();
        $this->assertFalse($second->headers->has('Content-Security-Policy'));
    }

    /**
     * CSP на /admin: nonce + unsafe-eval для Alpine (inline script/style — с nonce в Blade).
     */
    public function test_csp_for_filament_admin_uses_nonce_and_unsafe_eval(): void
    {
        $user = User::factory()->create(['locale' => 'ru']);
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/admin');
        $response->assertOk();
        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertNotSame('', $csp);
        $this->assertMatchesRegularExpression("/script-src[^;]*'nonce-[^']+'/", $csp);
        $this->assertStringContainsString("'unsafe-eval'", $csp);
        $this->assertStringNotContainsString('ui-avatars.com', $csp);
        $response->assertSee('nonce="', false);
    }

    /**
     * 5xx: без CSP — Ignition/Debugbar на странице ошибки используют inline без
     * nonce.
     */
    public function test_csp_is_omitted_on_server_error_response(): void
    {
        $middleware = app(SecurityHeaders::class);
        $request = Request::create('/login', 'GET');
        $response = $middleware->handle($request, fn (): Response => response('error',
            500));
        $this->assertFalse($response->headers->has('Content-Security-Policy'));
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
