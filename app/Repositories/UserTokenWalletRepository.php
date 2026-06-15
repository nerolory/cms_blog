<?php

namespace App\Repositories;

use App\DTO\PaymentResultData;
use App\DTO\TokenGrantData;
use App\Enums\TokenTransactionType;
use App\Models\TokenPackage;
use App\Models\TokenTransaction;
use App\Models\User;
use App\Models\UserTokenWallet;
use App\Repositories\Contracts\UserTokenWalletRepositoryContract;

/**
 * Репозиторий user token wallet.

 *
 * @property-read UserTokenWallet $wallet
 * @property-read TokenTransaction $transaction
 */
class UserTokenWalletRepository implements UserTokenWalletRepositoryContract
{
    public function __construct(protected UserTokenWallet $wallet, protected TokenTransaction $transaction) {}

    /**
     * Находит or create for user.
     *
     * @param  User  $user  пользователь

     * @return UserTokenWallet
     */
    public function findOrCreateForUser(User $user): UserTokenWallet
    {
        return $this->wallet->newQuery()->firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
    }

    /**
     * Возвращает balance.
     *
     * @param  User  $user  пользователь

     * @return int
     */
    public function getBalance(User $user): int
    {
        return $this->findOrCreateForUser($user)->balance;
    }

    /**
     * credit.
     *
     * @param  User  $user  пользователь

     * @return TokenTransaction
     */
    public function credit(User $user, int $amount, TokenGrantData $grant): TokenTransaction
    {
        $wallet = $this->findOrCreateForUser($user);
        $wallet->increment('balance', $amount);

        return $this->transaction->newQuery()->create(['user_id' => $user->id, 'amount' => $amount,
            'type' => TokenTransactionType::AdminGrant, 'reference_type' => User::class,
            'reference_id' => $grant->grantedByUserId, 'metadata' => ['note' => $grant->note]]);
    }

    /**
     * debit.
     *
     * @param  User  $user  пользователь
     * @param  string  $referenceType  type
     * @param  int  $referenceId  id

     * @return TokenTransaction
     */
    public function debit(User $user, int $amount, string $referenceType, int $referenceId,
        ?string $note = null): TokenTransaction
    {
        $wallet = $this->findOrCreateForUser($user);
        $wallet->decrement('balance', $amount);

        return $this->transaction->newQuery()->create(['user_id' => $user->id, 'amount' => -$amount,
            'type' => TokenTransactionType::Spend, 'reference_type' => $referenceType, 'reference_id' => $referenceId,
            'metadata' => $note === null ? null : ['note' => $note]]);
    }

    /**
     * purchase.
     *
     * @param  User  $user  пользователь

     * @return TokenTransaction
     */
    public function purchase(User $user, TokenPackage $package, PaymentResultData $payment): TokenTransaction
    {
        $wallet = $this->findOrCreateForUser($user);
        $wallet->increment('balance', $package->token_amount);

        return $this->transaction->newQuery()->create(['user_id' => $user->id, 'amount' => $package->token_amount,
            'type' => TokenTransactionType::Purchase, 'reference_type' => TokenPackage::class,
            'reference_id' => $package->id, 'metadata' => ['package_name' => $package->name,
                'price_cents' => $package->price_cents, 'currency' => $package->currency,
                'payment_gateway' => $payment->gateway, 'payment_transaction_id' => $payment->transactionId]]);
    }
}
