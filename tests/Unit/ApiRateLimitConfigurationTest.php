<?php

namespace Tests\Unit;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Класс api rate limit configuration.
 */
class ApiRateLimitConfigurationTest extends TestCase
{
    /**
     * API rate limiters are registered for read and write endpoints.
     */
    public function test_api_rate_limiters_are_registered(): void
    {
        $readLimiter = RateLimiter::limiter('api');
        $writeLimiter = RateLimiter::limiter('api-write');
        $this->assertNotNull($readLimiter);
        $this->assertNotNull($writeLimiter);
        $request = Request::create('/api/v1/posts', 'GET', server: ['REMOTE_ADDR' => '203.0.113.10']);
        $readLimit = $readLimiter($request);
        $writeLimit = $writeLimiter($request);
        $this->assertInstanceOf(Limit::class, $readLimit);
        $this->assertInstanceOf(Limit::class, $writeLimit);
        $this->assertSame(60, $readLimit->maxAttempts);
        $this->assertSame(30, $writeLimit->maxAttempts);
    }
}
