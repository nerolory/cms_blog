<?php

namespace Tests\Concerns;

use App\Support\Database\DatabaseProtection;
use Illuminate\Foundation\Testing\RefreshDatabase as BaseRefreshDatabase;

/**
 * Safe RefreshDatabase — refuses to run unless tests use sqlite :memory:.
 */
trait RefreshDatabase
{
    use BaseRefreshDatabase {
        refreshDatabase as baseRefreshDatabase;
    }

    /**
     * refresh database.
     */
    public function refreshDatabase(): void
    {
        DatabaseProtection::assertTestsUseIsolatedDatabase();
        $this->baseRefreshDatabase();
    }
}
