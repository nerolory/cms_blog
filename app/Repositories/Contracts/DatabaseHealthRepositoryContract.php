<?php

namespace App\Repositories\Contracts;

/**
 * Контракт проверки доступности СУБД.
 */
interface DatabaseHealthRepositoryContract
{
    /**
     * ping.

     *
     * @return bool
     */
    public function ping(): bool;
}
