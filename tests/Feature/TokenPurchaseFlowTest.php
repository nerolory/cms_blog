<?php

namespace Tests\Feature;

use App\Models\TokenPackage;
use App\Models\TokenTransaction;
use App\Models\User;
use App\Services\Contracts\TokenWalletServiceContract;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Класс token purchase flow.
 */
class TokenPurchaseFlowTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    /**
     * Подготавливает окружение теста.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    /**
     * test user can purchase package via mock payment gateway.
     */
    public function test_user_can_purchase_package_via_mock_payment_gateway(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $package = TokenPackage::query()->create(['name' => 'Starter', 'token_amount' => 100, 'price_cents' => 499,
            'currency' => 'RUB', 'is_active' => true, 'sort_order' => 1]);
        $this->actingAs($user)->post(route('tokens.packages.purchase',
            ['packageId' => $package->id]))->assertRedirect(route('tokens.show'));
        $this->assertSame(100, app(TokenWalletServiceContract::class)->getBalance($user));
        $transaction = TokenTransaction::query()->where('user_id', $user->id)->where('type', 'purchase')->first();
        $this->assertNotNull($transaction);
        $this->assertSame('mock', $transaction->metadata['payment_gateway'] ?? null);
        $this->assertNotEmpty($transaction->metadata['payment_transaction_id'] ?? null);
    }
}
