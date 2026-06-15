<?php

namespace App\Services\Contracts;

use App\DTO\TokenGrantData;
use App\Models\TokenPackage;
use App\Models\TokenTransaction;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Контракт сервиса token wallet.
 */
interface TokenWalletServiceContract
{
    /**
     * Возвращает balance.
     *
     * @param  User  $user  пользователь

     * @return int
     */
    public function getBalance(User $user): int;

    /**
     * grant.

     *
     * @return TokenTransaction
     */
    public function grant(TokenGrantData $grant): TokenTransaction;

    /**
     * purchase package.
     *
     * @param  User  $user  пользователь

     * @return TokenTransaction
     */
    public function purchasePackage(User $user, TokenPackage $package): TokenTransaction;

    /**
     * Покупка пакета по id (web).

     *
     * @return TokenTransaction
     */
    public function purchasePackageById(User $user, int $packageId): TokenTransaction;

    /**
     * Активные пакеты токенов для страницы покупки.
     *
     * @return Collection<int, TokenPackage>
     */
    public function listActivePackages(): Collection;

    /**
     * ensure sufficient balance.
     *
     * @param  User  $user  пользователь
     */
    public function ensureSufficientBalance(User $user, int $required): void;
}
