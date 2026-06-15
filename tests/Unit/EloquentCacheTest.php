<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\User;
use App\Support\Cache\EloquentCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

/**
 * Класс eloquent cache.
 */
class EloquentCacheTest extends TestCase
{
    use RefreshDatabase;

    /**
     * test paginator cache round trip preserves models and pagination.
     */
    public function test_paginator_cache_round_trip_preserves_models_and_pagination(): void
    {
        $author = User::factory()->create(['name' => 'Cached Author']);
        Post::factory()->count(12)->for($author)->create();
        $resolver = fn (): LengthAwarePaginator => Post::query()->with('user')->orderByDesc('id')->paginate(10);
        $original = $resolver();
        $payload = EloquentCache::serializePaginator($original);
        $restored = EloquentCache::deserializePaginator($payload);
        $this->assertSame(12, $restored->total());
        $this->assertSame(10, $restored->perPage());
        $this->assertSame(1, $restored->currentPage());
        $this->assertCount(10, $restored->items());
        /** @var Post $firstPost */
        $firstPost = $restored->items()[0];
        $this->assertSame('Cached Author', $firstPost->user?->name);
    }

    /**
     * test remember paginator works twice with array cache payload.
     */
    public function test_remember_paginator_works_twice_with_array_cache_payload(): void
    {
        $author = User::factory()->create();
        Post::factory()->count(3)->for($author)->create();
        $key = 'test.posts.listing.v2';
        $first = EloquentCache::rememberPaginator($key, 60,
            fn (): LengthAwarePaginator => Post::query()->with('user')->paginate(10));
        $second = EloquentCache::rememberPaginator($key, 60,
            fn (): LengthAwarePaginator => Post::query()->with('user')->paginate(999));
        $this->assertSame(3, $first->total());
        $this->assertSame(3, $second->total());
    }
}
