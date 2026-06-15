<?php

namespace App\Repositories\Contracts;

use App\DTO\PaymentResultData;
use App\DTO\TokenGrantData;
use App\Models\TokenPackage;
use App\Models\TokenTransaction;
use App\Models\User;
use App\Models\UserTokenWallet;

/**
 * Контракт репозитория user token wallet.
 */
interface UserTokenWalletRepositoryContract
{
    /**
     * Находит or create for user.
     *
     * @param  User  $user  пользователь

     * @return UserTokenWallet
     */
    public function findOrCreateForUser(User $user): UserTokenWallet;

    /**
     * Возвращает balance.
     *
     * @param  User  $user  пользователь

     * @return int
     */
    public function getBalance(User $user): int;

    /**
     * credit.
     *
     * @param  User  $user  пользователь

     * @return TokenTransaction
     */
    public function credit(User $user, int $amount, TokenGrantData $grant): TokenTransaction;

    /**
     * debit.
     *
     * @param  User  $user  пользователь
     * @param  string  $referenceType  type
     * @param  int  $referenceId  id

     * @return TokenTransaction
     */
    public function debit(User $user, int $amount, string $referenceType, int $referenceId,
        ?string $note = null): TokenTransaction;

    /**
     * purchase.
     *
     * @param  User  $user  пользователь

     * @return TokenTransaction
     */
    public function purchase(User $user, TokenPackage $package, PaymentResultData $payment): TokenTransaction;
}
