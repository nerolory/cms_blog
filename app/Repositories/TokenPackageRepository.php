<?php

namespace App\Repositories;

use App\Models\TokenPackage;
use App\Repositories\Contracts\TokenPackageRepositoryContract;
use Illuminate\Support\Collection;

/**
 * Репозиторий token package.
 *
 * @property-read TokenPackage $package
 */
class TokenPackageRepository implements TokenPackageRepositoryContract
{
    public function __construct(protected TokenPackage $package) {}

    /**
     * list active.
     */ /**
     * Возвращает активные пакеты токенов.
     *
     * @return Collection<int, TokenPackage>
     */
    public function listActive(): Collection
    {
        return $this->package->newQuery()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();
    }

    /**
     * Находит by id.

     *
     * @return ?TokenPackage
     */
    public function findById(int $id): ?TokenPackage
    {
        return $this->package->newQuery()->find($id);
    }
}
