<?php

namespace App\Services;

use App\DTO\PaymentChargeData;
use App\DTO\TokenGrantData;
use App\Exceptions\InsufficientTokensException;
use App\Exceptions\PaymentAwaitingWebhookException;
use App\Exceptions\PaymentFailedException;
use App\Models\TokenPackage;
use App\Models\TokenTransaction;
use App\Models\User;
use App\Repositories\Contracts\PaymentIntentRepositoryContract;
use App\Repositories\Contracts\TokenPackageRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Repositories\Contracts\UserTokenWalletRepositoryContract;
use App\Services\Contracts\PaymentGatewayContract;
use App\Services\Contracts\PaymentWebhookServiceContract;
use App\Services\Contracts\TokenWalletServiceContract;
use App\Support\Payment\PaymentGatewayResolver;
use Illuminate\Support\Collection;

/**
 * Сервис token wallet.
 *
 * @property-read UserTokenWalletRepositoryContract $wallets
 * @property-read PaymentGatewayContract $paymentGateway
 * @property-read TokenPackageRepositoryContract $packages
 * @property-read UserRepositoryContract $users
 * @property-read PaymentIntentRepositoryContract $paymentIntents
 * @property-read PaymentWebhookServiceContract $paymentWebhooks
 */
class TokenWalletService implements TokenWalletServiceContract
{
    public function __construct(protected UserTokenWalletRepositoryContract $wallets,
        protected PaymentGatewayContract $paymentGateway, protected TokenPackageRepositoryContract $packages,
        protected UserRepositoryContract $users, protected PaymentIntentRepositoryContract $paymentIntents,
        protected PaymentWebhookServiceContract $paymentWebhooks) {}

    /**
     * Возвращает balance.
     *
     * @param  User  $user  пользователь

     * @return int
     */
    public function getBalance(User $user): int
    {
        return $this->wallets->getBalance($user);
    }

    /**
     * grant.

     *
     * @return TokenTransaction
     */
    public function grant(TokenGrantData $grant): TokenTransaction
    {
        $user = $this->users->findByIdOrFail($grant->userId);

        return $this->wallets->credit($user, $grant->amount, $grant);
    }

    /**
     * purchase package.
     *
     * @param  User  $user  пользователь

     * @return TokenTransaction
     */
    public function purchasePackage(User $user, TokenPackage $package): TokenTransaction
    {
        if (! $package->is_active) {
            throw new \RuntimeException(__('tokens.errors.package_inactive'));
        }
        $payment = $this->paymentGateway->charge(new PaymentChargeData(userId: $user->id,
            amountCents: $package->price_cents, currency: $package->currency,
            description: __('tokens.page.package_amount', ['amount' => $package->token_amount]),
            reference: 'token_package:'.$package->id));
        if (! $payment->successful) {
            throw PaymentFailedException::forReason($payment->failureReason ?? 'unknown');
        }

        $intent = $this->paymentIntents->createPending($user, $package, $payment);
        $gateway = PaymentGatewayResolver::normalizeGatewayName($payment->gateway);
        if ($gateway === 'mock') {
            return $this->paymentWebhooks->confirmMockPayment($intent, $payment);
        }

        throw PaymentAwaitingWebhookException::forIntent();
    }

    /**
     * {@inheritdoc}

     *
     * @return TokenTransaction
     */
    public function purchasePackageById(User $user, int $packageId): TokenTransaction
    {
        $package = $this->packages->findById($packageId);
        if ($package === null) {
            abort(404);
        }

        return $this->purchasePackage($user, $package);
    }

    /**
     * Возвращает активные пакеты токенов.
     *
     * @return Collection<int, TokenPackage>
     */
    public function listActivePackages(): Collection
    {
        return $this->packages->listActive();
    }

    /**
     * ensure sufficient balance.
     *
     * @param  User  $user  пользователь
     */
    public function ensureSufficientBalance(User $user, int $required): void
    {
        $available = $this->getBalance($user);
        if ($available < $required) {
            throw InsufficientTokensException::forBalance($required, $available);
        }
    }
}
