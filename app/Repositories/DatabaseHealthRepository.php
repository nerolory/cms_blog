<?php

namespace App\Repositories;

use App\Repositories\Contracts\DatabaseHealthRepositoryContract;
use Illuminate\Support\Facades\DB;

/**
 * Репозиторий ping-запросов к СУБД для health probe.
 */
class DatabaseHealthRepository implements DatabaseHealthRepositoryContract
{
    /**
     * ping.

     *
     * @return bool
     */
    public function ping(): bool
    {
        DB::select('select 1 as ok');

        return true;
    }
}
