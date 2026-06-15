<?php

namespace Tests\Feature;

use App\Models\Post;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

/**
 * Класс api rate limit.
 */
class ApiRateLimitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Throttle middleware exposes rate limit headers on API responses.
     */
    public function test_api_responses_include_rate_limit_headers(): void
    {
        Post::factory()->published()->create();
        $this->getJson('/api/v1/posts')->assertOk()->assertHeader('X-RateLimit-Limit', 60);
    }
}
