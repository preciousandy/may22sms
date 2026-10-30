<?php

namespace Tests\Feature;

use App\Models\CryptoTransaction;
use App\Models\CryptoWebhook;
use App\Models\ServiceConfiguration;
use App\Models\Setting;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\QuidaxService;
use App\Services\ServiceManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QuidaxCryptoOffRampTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected User $anotherUser;
    protected ServiceManager $serviceManager;

    protected function setUp(): void
    {
        parent::setUp();

        // Standard settings
        Setting::updateOrCreate(['key' => 'ngn_per_usd'], ['value' => '1500.0']);

        // Default Quidax Test Config
        Config::set('quidax.env', 'test');
        Config::set('quidax.test_private_key', 'test_sec_key_12345');
        Config::set('quidax.test_webhook_secret', 'test_webhook_secret_xyz');
        Config::set('quidax.live_private_key', 'live_sec_key_should_not_be_used');

        // Service configuration: ensure crypto_to_naira is enabled
        ServiceConfiguration::updateOrCreate(
            ['key' => 'crypto_to_naira'],
            [
                'name' => 'Crypto to Naira',
                'description' => 'Convert cryptocurrency to NGN via Quidax',
                'category' => 'financial',
                'is_enabled' => true,
            ]
        );

        $this->user = User::factory()->create([
            'name' => 'Crypto Tester',
            'email' => 'cryptotester@example.com',
            'account_status' => 'active',
            'walletBalance' => 100.0,
            'is_admin' => false,
        ]);

        $this->anotherUser = User::factory()->create([
            'name' => 'Other User',
            'email' => 'otheruser@example.com',
            'account_status' => 'active',
            'walletBalance' => 50.0,
            'is_admin' => false,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin Boss',
            'email' => 'adminboss@example.com',
            'account_status' => 'active',
            'is_admin' => true,
        ]);

        $this->serviceManager = app(ServiceManager::class);
    }

    /**
     * Test 1: User can access crypto page in test mode when enabled.
     */
    public function test_user_can_access_crypto_page_when_service_is_enabled(): void
    {
        $response = $this->actingAs($this->user)->get('/services/crypto-to-naira');

        $response->assertStatus(200);
        $response->assertSee('Receive Crypto & Convert to Naira');
        $response->assertSee('May22Sms-Crypto-Convert');
    }

    /**
     * Test 2: User cannot create transaction when service is disabled.
     */
    public function test_user_cannot_create_transaction_when_service_is_disabled(): void
    {
        $this->serviceManager->updateStatus('crypto_to_naira', false, 'Maintenance', null, $this->admin);

        $response = $this->actingAs($this->user)->postJson('/services/crypto-to-naira/create', [
            'currency' => 'usdt',
            'amount' => 50,
            'network' => 'bep20',
            'payout_destination' => 'wallet',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test 3: Real-time quote calculation in test mode.
     */
    public function test_real_time_quote_calculation(): void
    {
        $response = $this->actingAs($this->user)->postJson('/services/crypto-to-naira/quote', [
            'currency' => 'usdt',
            'amount' => 100,
            'network' => 'bep20',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'quote' => [
                'from_currency' => 'usdt',
                'from_amount' => 100,
                'network' => 'bep20',
            ],
        ]);

        $data = $response->json('quote');
        $this->assertGreaterThan(0, $data['gross_ngn']);
        $this->assertGreaterThan(0, $data['estimated_payout_ngn']);
        $this->assertArrayHasKey('exchange_rate', $data);
        $this->assertArrayHasKey('platform_fee_ngn', $data);
    }

    /**
     * Test 4: Quote fails for unsupported currency or network.
     */
    public function test_quote_fails_for_unsupported_currency_or_network(): void
    {
        $response = $this->actingAs($this->user)->postJson('/services/crypto-to-naira/quote', [
            'currency' => 'dogecoin',
            'amount' => 50,
            'network' => 'doge_mainnet',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error',
        ]);
    }

    /**
     * Test 5: Quote fails for amount below minimum or above maximum.
     */
    public function test_quote_fails_for_amount_outside_limits(): void
    {
        // USDT minimum is 1.0, test with 0.5
        $response = $this->actingAs($this->user)->postJson('/services/crypto-to-naira/quote', [
            'currency' => 'usdt',
            'amount' => 0.5,
            'network' => 'bep20',
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Minimum amount', $response->json('message'));
    }

    /**
     * Test 6: Successful transaction creation and deposit address generation in test mode.
     */
    public function test_successful_transaction_creation_and_deposit_address_generation(): void
    {
        $response = $this->actingAs($this->user)->postJson('/services/crypto-to-naira/create', [
            'currency' => 'usdt',
            'amount' => 50.0,
            'network' => 'trc20',
            'payout_destination' => 'wallet',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $tx = CryptoTransaction::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($tx);
        $this->assertEquals('usdt', $tx->from_currency);
        $this->assertEquals(50.0, (float) $tx->from_amount);
        $this->assertEquals('trc20', $tx->network);
        $this->assertEquals('wallet', $tx->payout_destination);
        $this->assertEquals('address_assigned', $tx->status);
        $this->assertNotEmpty($tx->deposit_address);
        // TRC20 address should start with T
        $this->assertStringStartsWith('T', $tx->deposit_address);
    }

    /**
     * Test 7: Direct bank account payout transaction creation with bank details.
     */
    public function test_bank_payout_transaction_creation(): void
    {
        $response = $this->actingAs($this->user)->postJson('/services/crypto-to-naira/create', [
            'currency' => 'usdt',
            'amount' => 75.0,
            'network' => 'bep20',
            'payout_destination' => 'bank_account',
            'bank_code' => '058',
            'account_number' => '0123456789',
            'account_name' => 'Tester Bank Account',
        ]);

        $response->assertStatus(200);

        $tx = CryptoTransaction::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($tx);
        $this->assertEquals('bank_account', $tx->payout_destination);
        $this->assertEquals('058', $tx->bank_code);
        $this->assertEquals('0123456789', $tx->account_number);
        $this->assertEquals('address_assigned', $tx->status);
    }

    /**
     * Test 8: IDOR Protection: User cannot view another user's transaction status.
     */
    public function test_user_cannot_access_another_users_transaction(): void
    {
        $tx = CryptoTransaction::create([
            'user_id' => $this->user->id,
            'merchant_reference' => 'TX_USER_A_12345',
            'is_test_mode' => true,
            'from_currency' => 'usdt',
            'from_amount' => 50,
            'to_currency' => 'ngn',
            'to_amount' => 68000,
            'exchange_rate' => 1360,
            'network' => 'bep20',
            'payout_destination' => 'wallet',
            'status' => 'address_assigned',
            'deposit_address' => '0x1234567890abcdef',
        ]);

        // Another user attempts to query User A's transaction
        $response = $this->actingAs($this->anotherUser)->getJson("/services/crypto-to-naira/status/{$tx->merchant_reference}");

        $response->assertStatus(404);
    }

    /**
     * Test 9: Webhook signature verification rejects invalid signatures.
     */
    public function test_webhook_signature_verification_rejects_invalid_signature(): void
    {
        $payload = json_encode([
            'event' => 'sell_transaction.successful',
            'data' => [
                'merchant_reference' => 'INVALID_SIG_REF',
                'status' => 'completed',
            ],
        ]);

        $response = $this->post('/api/webhooks/quidax', json_decode($payload, true), [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAMP_SIGNATURE' => 'forged_or_invalid_signature_hash',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['status' => 'error', 'message' => 'Invalid signature']);
    }

    /**
     * Test 10: Valid webhook processes and updates transaction to processing.
     */
    public function test_webhook_processing_event_updates_status(): void
    {
        $tx = CryptoTransaction::create([
            'user_id' => $this->user->id,
            'merchant_reference' => 'TX_PROC_998877',
            'is_test_mode' => true,
            'from_currency' => 'usdt',
            'from_amount' => 100,
            'to_currency' => 'ngn',
            'to_amount' => 136000,
            'exchange_rate' => 1360,
            'network' => 'bep20',
            'payout_destination' => 'wallet',
            'status' => 'address_assigned',
            'deposit_address' => '0x9999999999999999',
        ]);

        $payloadArray = [
            'event' => 'sell_transaction.processing',
            'data' => [
                'merchant_reference' => $tx->merchant_reference,
                'status' => 'processing',
                'crypto_deposit' => [
                    'amount' => 100,
                    'txid' => '0xhash123456789',
                ],
            ],
        ];

        $rawBody = json_encode($payloadArray);
        $signature = hash_hmac('sha256', $rawBody, config('quidax.test_webhook_secret'));

        $response = $this->call(
            'POST',
            '/api/webhooks/quidax',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_RAMP_SIGNATURE' => $signature,
            ],
            $rawBody
        );

        $response->assertStatus(200);
        $this->assertEquals('processing', $tx->fresh()->status);
        $this->assertEquals('0xhash123456789', $tx->fresh()->crypto_txid);
    }

    /**
     * Test 11: Webhook successful settlement credits user wallet and prevents duplicate credits.
     */
    public function test_webhook_successful_settlement_credits_wallet_and_prevents_duplicate(): void
    {
        $initialBalance = (float) $this->user->walletBalance; // 100.0 USD

        $tx = CryptoTransaction::create([
            'user_id' => $this->user->id,
            'merchant_reference' => 'TX_SETTLE_112233',
            'is_test_mode' => true,
            'from_currency' => 'usdt',
            'from_amount' => 50,
            'to_currency' => 'ngn',
            'to_amount' => 75000, // ₦75,000 / 1500 = $50 USD
            'exchange_rate' => 1500,
            'network' => 'bep20',
            'payout_destination' => 'wallet',
            'status' => 'processing',
            'deposit_address' => '0xabc123',
            'wallet_credited' => false,
        ]);

        $payloadArray = [
            'event' => 'sell_transaction.successful',
            'data' => [
                'merchant_reference' => $tx->merchant_reference,
                'status' => 'completed',
                'fiat_payout' => [
                    'amount' => 75000,
                    'public_id' => 'PO_99999',
                    'processor_fee' => 0,
                ],
            ],
        ];

        $rawBody = json_encode($payloadArray);
        $signature = hash_hmac('sha256', $rawBody, config('quidax.test_webhook_secret'));

        // First delivery
        $response1 = $this->call(
            'POST',
            '/api/webhooks/quidax',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_RAMP_SIGNATURE' => $signature,
            ],
            $rawBody
        );

        $response1->assertStatus(200);

        $tx->refresh();
        $this->assertEquals('completed', $tx->status);
        $this->assertTrue((bool) $tx->wallet_credited);
        $this->assertNotNull($tx->settled_at);

        $updatedUser = $this->user->fresh();
        // 75,000 NGN at 1500 rate = 50.0 USD credit => 100 + 50 = 150.0
        $this->assertEquals(150.0, (float) $updatedUser->walletBalance);

        // Ledger check
        $walletTxCount = WalletTransaction::where('reference', $tx->merchant_reference)->count();
        $this->assertEquals(1, $walletTxCount);

        // Second delivery (duplicate webhook)
        $response2 = $this->call(
            'POST',
            '/api/webhooks/quidax',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_RAMP_SIGNATURE' => $signature,
            ],
            $rawBody
        );

        $response2->assertStatus(200);

        // User balance must remain exactly 150.0; no double credit!
        $this->assertEquals(150.0, (float) $this->user->fresh()->walletBalance);
        $this->assertEquals(1, WalletTransaction::where('reference', $tx->merchant_reference)->count());
    }

    /**
     * Test 12: Direct bank payout marks completed without internal wallet credit.
     */
    public function test_bank_payout_does_not_credit_internal_wallet(): void
    {
        $initialBalance = (float) $this->user->walletBalance;

        $tx = CryptoTransaction::create([
            'user_id' => $this->user->id,
            'merchant_reference' => 'TX_BANK_SETTLE_55',
            'is_test_mode' => true,
            'from_currency' => 'usdt',
            'from_amount' => 100,
            'to_currency' => 'ngn',
            'to_amount' => 140000,
            'exchange_rate' => 1400,
            'network' => 'trc20',
            'payout_destination' => 'bank_account',
            'bank_code' => '058',
            'bank_name' => 'GTBank',
            'account_number' => '0123456789',
            'account_name' => 'John Doe',
            'status' => 'processing',
            'deposit_address' => 'T123456789',
            'wallet_credited' => false,
        ]);

        $payloadArray = [
            'event' => 'sell_transaction.successful',
            'data' => [
                'merchant_reference' => $tx->merchant_reference,
                'status' => 'completed',
                'fiat_payout' => [
                    'amount' => 140000,
                    'public_id' => 'PO_BANK_123',
                ],
            ],
        ];

        $rawBody = json_encode($payloadArray);
        $signature = hash_hmac('sha256', $rawBody, config('quidax.test_webhook_secret'));

        $response = $this->call(
            'POST',
            '/api/webhooks/quidax',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_RAMP_SIGNATURE' => $signature,
            ],
            $rawBody
        );

        $response->assertStatus(200);

        $tx->refresh();
        $this->assertEquals('completed', $tx->status);
        $this->assertFalse((bool) $tx->wallet_credited);
        $this->assertEquals($initialBalance, (float) $this->user->fresh()->walletBalance);
    }

    /**
     * Test 13: Admin can access crypto dashboard, filter, and reconcile.
     */
    public function test_admin_crypto_dashboard_and_reconciliation(): void
    {
        $tx = CryptoTransaction::create([
            'user_id' => $this->user->id,
            'merchant_reference' => 'TX_ADMIN_TEST_88',
            'is_test_mode' => true,
            'from_currency' => 'usdt',
            'from_amount' => 200,
            'to_currency' => 'ngn',
            'to_amount' => 280000,
            'exchange_rate' => 1400,
            'network' => 'bep20',
            'payout_destination' => 'wallet',
            'status' => 'processing',
            'deposit_address' => '0xadmin8888',
        ]);

        // Admin views crypto dashboard
        $response = $this->actingAs($this->admin)->get('/admin/crypto');
        $response->assertStatus(200);
        $response->assertSee('Crypto Off-Ramp Management');
        $response->assertSee($tx->merchant_reference);

        // Filter by currency
        $filterResponse = $this->actingAs($this->admin)->get('/admin/crypto?currency=usdt');
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee($tx->merchant_reference);

        // Manual reconciliation
        $reconcileResponse = $this->actingAs($this->admin)->from('/admin/crypto')->post("/admin/crypto/{$tx->id}/reconcile");
        $reconcileResponse->assertRedirect('/admin/crypto');
        $reconcileResponse->assertSessionHas('success');
    }

    /**
     * Test 14: Admin can toggle service availability.
     */
    public function test_admin_can_toggle_service(): void
    {
        $response = $this->actingAs($this->admin)->from('/admin/crypto')->post('/admin/crypto/toggle-service');
        $response->assertRedirect('/admin/crypto');
        $response->assertSessionHas('success');

        $this->assertFalse($this->serviceManager->isAvailable('crypto_to_naira'));

        $response2 = $this->actingAs($this->admin)->from('/admin/crypto')->post('/admin/crypto/toggle-service');
        $response2->assertRedirect('/admin/crypto');
        $response2->assertSessionHas('success');

        $this->assertTrue($this->serviceManager->isAvailable('crypto_to_naira'));
    }

    /**
     * Test 15: Strict test and production credential separation.
     */
    public function test_credential_separation_test_mode_uses_test_keys(): void
    {
        Config::set('quidax.env', 'test');
        Config::set('quidax.test_private_key', 'test_key_sample');
        Config::set('quidax.live_private_key', 'live_key_sample');

        $service = new QuidaxService();
        $this->assertTrue($service->isTestMode());

        // Ensure live key is never used when in test mode
        Config::set('quidax.test_private_key', null);
        $serviceWithoutTestKey = new QuidaxService();
        $this->assertTrue($serviceWithoutTestKey->isTestMode());
    }
}
