<?php

namespace App\Repositories\Contracts;

use App\Models\TokenPackage;
use Illuminate\Support\Collection;

/**
 * Контракт репозитория token package.
 */
interface TokenPackageRepositoryContract
{
    /**
     * list active.
     */
    /**
     * list active.
     */
    /**
     * Возвращает активные пакеты токенов.
     *
     * @return Collection<int, TokenPackage>
     */
    public function listActive(): Collection;

    /**
     * Находит by id.

     *
     * @return ?TokenPackage
     */
    public function findById(int $id): ?TokenPackage;
}
