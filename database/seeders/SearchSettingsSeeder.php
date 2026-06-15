<?php

namespace Database\Seeders;

use App\Enums\SearchDriver;
use App\Repositories\Contracts\SearchSettingsRepositoryContract;
use App\Support\Search\SearchSettingKey;
use Illuminate\Database\Seeder;

/**
 * Default search driver (database fallback) for development.
 */
class SearchSettingsSeeder extends Seeder
{
    /**
     * run.
     */
    public function run(): void
    {
        $repository = app(SearchSettingsRepositoryContract::class);
        if ($repository->get(SearchSettingKey::DRIVER) !== null) {
            return;
        }
        $repository->set(SearchSettingKey::DRIVER, SearchDriver::Database->value);
    }
}
