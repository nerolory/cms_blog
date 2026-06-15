<?php

namespace Tests\Feature;

use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

/**
 * Класс example.
 */
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Home and posts index return successful responses.
     */
    public function test_home_and_posts_index_return_successful_response(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('posts.index'))->assertOk();
    }
}
