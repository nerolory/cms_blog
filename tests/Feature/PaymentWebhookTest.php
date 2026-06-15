<?php

namespace Tests\Feature;

use App\Models\PaymentIntent;
use App\Models\TokenPackage;
use App\Models\User;
use App\Services\Contracts\PaymentGatewayContract;
use App\Services\Contracts\TokenWalletServiceContract;
use App\Services\Payment\HttpPaymentGateway;
use App\Services\Payment\MockPaymentGateway;
use App\Support\Payment\PaymentWebhookSigner;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Verified payment webhook: единственный путь зачисления токенов для HTTP gateway.
 */
class PaymentWebhookTest extends TestCase
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
        config(['payment.webhook.secret' => 'test-webhook-secret']);
    }

    /**
     * test mock purchase credits tokens via verified webhook pipeline.
     */
    public function test_mock_purchase_credits_tokens_via_verified_webhook_pipeline(): void
    {
        config(['payment.gateway' => 'mock']);
        $this->app->bind(PaymentGatewayContract::class, MockPaymentGateway::class);
        $this->app->forgetInstance(TokenWalletServiceContract::class);

        $user = User::factory()->create();
        $user->assignRole('user');
        $package = TokenPackage::query()->create([
            'name' => 'Starter',
            'token_amount' => 50,
            'price_cents' => 299,
            'currency' => 'RUB',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($user)->post(route('tokens.packages.purchase', ['packageId' => $package->id]))
            ->assertRedirect(route('tokens.show'));

        $this->assertSame(50, app(TokenWalletServiceContract::class)->getBalance($user));
        $this->assertDatabaseHas('payment_intents', [
            'user_id' => $user->id,
            'token_package_id' => $package->id,
            'gateway' => 'mock',
            'status' => 'succeeded',
        ]);
    }

    /**
     * test http purchase awaits webhook and credits on signed callback.
     */
    public function test_http_purchase_awaits_webhook_and_credits_on_signed_callback(): void
    {
        config([
            'payment.gateway' => 'http',
            'payment.http.base_url' => 'https://payments.test',
            'payment.http.secret' => 'api-secret',
        ]);
        $this->app->bind(PaymentGatewayContract::class, HttpPaymentGateway::class);
        $this->app->forgetInstance(TokenWalletServiceContract::class);

        Http::fake([
            'https://payments.test/charges' => Http::response([
                'successful' => true,
                'transaction_id' => 'pay_http_99',
            ], 200),
        ]);

        $user = User::factory()->create();
        $user->assignRole('user');
        $package = TokenPackage::query()->create([
            'name' => 'Pro',
            'token_amount' => 200,
            'price_cents' => 999,
            'currency' => 'RUB',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($user)->post(route('tokens.packages.purchase', ['packageId' => $package->id]))
            ->assertRedirect(route('tokens.show'))
            ->assertSessionHas('success');

        $this->assertSame(0, app(TokenWalletServiceContract::class)->getBalance($user));
        $intent = PaymentIntent::query()->where('gateway_transaction_id', 'pay_http_99')->first();
        $this->assertNotNull($intent);
        $this->assertSame('pending', $intent->status->value);

        $payload = json_encode([
            'event' => 'payment.succeeded',
            'transaction_id' => 'pay_http_99',
            'reference' => 'token_package:'.$package->id,
            'gateway' => 'http',
        ], JSON_THROW_ON_ERROR);
        $signature = app(PaymentWebhookSigner::class)->sign($payload);

        /** @var array<string, mixed> $body */
        $body = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        $this->postJson(route('webhooks.payment'), $body, [
            'X-Payment-Signature' => $signature,
        ])->assertOk()->assertJson(['processed' => true]);

        $this->assertSame(200, app(TokenWalletServiceContract::class)->getBalance($user));
    }

    /**
     * test webhook rejects invalid signature.
     */
    public function test_webhook_rejects_invalid_signature(): void
    {
        $payload = json_encode([
            'event' => 'payment.succeeded',
            'transaction_id' => 'pay_bad',
            'reference' => 'token_package:1',
            'gateway' => 'http',
        ], JSON_THROW_ON_ERROR);

        /** @var array<string, mixed> $body */
        $body = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        $this->postJson(route('webhooks.payment'), $body, [
            'X-Payment-Signature' => 'sha256=invalid',
        ])->assertUnauthorized();
    }
}
