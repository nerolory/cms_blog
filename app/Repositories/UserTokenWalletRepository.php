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
use App\Support\Cache\ApplicationCacheKeys;
use App\Support\TypeCast;
use Illuminate\Support\Facades\Cache;

/**
 * Репозиторий user token wallet.
 *
 * @property-read UserTokenWallet $wallet
 * @property-read TokenTransaction $transaction
 */
class UserTokenWalletRepository implements UserTokenWalletRepositoryContract
{
    private const BALANCE_CACHE_TTL_SECONDS = 300;

    /** @var array<int, int> */
    private array $balanceByUserId = [];

    public function __construct(protected UserTokenWallet $wallet, protected TokenTransaction $transaction) {}

    /**
     * Находит or create for user.
     */
    public function findOrCreateForUser(User $user): UserTokenWallet
    {
        return $this->wallet->newQuery()->firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
    }

    /**
     * Возвращает balance.
     */
    public function getBalance(User $user): int
    {
        if (array_key_exists($user->id, $this->balanceByUserId)) {
            return $this->balanceByUserId[$user->id];
        }
        $cacheKey = ApplicationCacheKeys::userTokenBalance($user->id);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $this->balanceByUserId[$user->id] = TypeCast::int($cached);
        }
        $balance = TypeCast::int($this->wallet->newQuery()->where('user_id', $user->id)->value('balance') ?? 0);
        Cache::put($cacheKey, $balance, self::BALANCE_CACHE_TTL_SECONDS);

        return $this->balanceByUserId[$user->id] = $balance;
    }

    /**
     * credit.
     */
    public function credit(User $user, int $amount, TokenGrantData $grant): TokenTransaction
    {
        $wallet = $this->findOrCreateForUser($user);
        $wallet->increment('balance', $amount);
        $this->forgetBalanceCache($user->id);

        return $this->transaction->newQuery()->create(['user_id' => $user->id, 'amount' => $amount,
            'type' => TokenTransactionType::AdminGrant, 'reference_type' => User::class,
            'reference_id' => $grant->grantedByUserId, 'metadata' => ['note' => $grant->note]]);
    }

    /**
     * debit.
     */
    public function debit(User $user, int $amount, string $referenceType, int $referenceId,
        ?string $note = null): TokenTransaction
    {
        $wallet = $this->findOrCreateForUser($user);
        $wallet->decrement('balance', $amount);
        $this->forgetBalanceCache($user->id);

        return $this->transaction->newQuery()->create(['user_id' => $user->id, 'amount' => -$amount,
            'type' => TokenTransactionType::Spend, 'reference_type' => $referenceType, 'reference_id' => $referenceId,
            'metadata' => $note === null ? null : ['note' => $note]]);
    }

    /**
     * purchase.
     */
    public function purchase(User $user, TokenPackage $package, PaymentResultData $payment): TokenTransaction
    {
        $wallet = $this->findOrCreateForUser($user);
        $wallet->increment('balance', $package->token_amount);
        $this->forgetBalanceCache($user->id);

        return $this->transaction->newQuery()->create(['user_id' => $user->id, 'amount' => $package->token_amount,
            'type' => TokenTransactionType::Purchase, 'reference_type' => TokenPackage::class,
            'reference_id' => $package->id, 'metadata' => ['package_name' => $package->name,
                'price_cents' => $package->price_cents, 'currency' => $package->currency,
                'payment_gateway' => $payment->gateway, 'payment_transaction_id' => $payment->transactionId]]);
    }

    private function forgetBalanceCache(int $userId): void
    {
        unset($this->balanceByUserId[$userId]);
        Cache::forget(ApplicationCacheKeys::userTokenBalance($userId));
    }
}
