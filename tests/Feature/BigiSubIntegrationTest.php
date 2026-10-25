<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Models\UtilityTransaction;
use App\Services\BigiSubService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BigiSubIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure standard exchange rate
        Setting::updateOrCreate(
            ['key' => 'ngn_per_usd'],
            ['value' => '1500']
        );

        // Configure BigiSub settings in simulation mode for deterministic testing
        Setting::updateOrCreate(
            ['key' => 'bigisub_api_token'],
            ['value' => 'test_token_xyz123']
        );
        Setting::updateOrCreate(
            ['key' => 'bigisub_pin'],
            ['value' => '1234']
        );
        Setting::updateOrCreate(
            ['key' => 'bigisub_base_url'],
            ['value' => 'https://api.bigisub.ng']
        );
        Setting::updateOrCreate(
            ['key' => 'bigisub_simulate'],
            ['value' => '1']
        );
        Setting::updateOrCreate(
            ['key' => 'utility_airtime_discount_percent'],
            ['value' => '2.0']
        );

        // Create test user with $100 USD (equivalent to 150,000 NGN)
        $this->user = User::factory()->create([
            'name' => 'Alice Tech',
            'email' => 'alice.tech@example.com',
            'walletBalance' => 100.00000000,
            'account_status' => 'active',
            'is_admin' => false,
        ]);

        // Create other user with low balance ($10 USD = 15,000 NGN)
        $this->otherUser = User::factory()->create([
            'name' => 'Bob Marley',
            'email' => 'bob.marley@example.com',
            'walletBalance' => 10.00000000,
            'account_status' => 'active',
            'is_admin' => false,
        ]);

        // Create admin user
        $this->admin = User::factory()->create([
            'name' => 'Admin Controller',
            'email' => 'admin.controller@example.com',
            'walletBalance' => 500.00000000,
            'account_status' => 'active',
            'is_admin' => true,
        ]);
    }

    /**
     * Test utilities hub page loads for authenticated users.
     */
    public function test_user_can_view_utilities_hub(): void
    {
        $response = $this->actingAs($this->user)->get(route('utilities.index'));
        $response->assertStatus(200);
        $response->assertSee('Airtime');
        $response->assertSee('Data Bundles');
        $response->assertSee('Electricity');
        $response->assertSee('Betting');
    }

    /**
     * Test successful airtime purchase flow with wallet deduction and 2% discount.
     */
    public function test_successful_airtime_purchase(): void
    {
        $initialBalance = (float) $this->user->walletBalance; // $100.00
        $airtimeAmountNgn = 1000.0; // ₦1,000 face value

        // 2% discount = ₦20 discount. User pays ₦980 = $0.65333333 USD @ 1500 NGN/$
        $response = $this->actingAs($this->user)->postJson(route('utilities.purchase.airtime'), [
            'network' => 'mtn',
            'phone' => '08031234567',
            'amount' => $airtimeAmountNgn,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'completed',
        ]);

        // Verify database record
        $this->assertDatabaseHas('utility_transactions', [
            'user_id' => $this->user->id,
            'service_type' => 'airtime',
            'network_or_disco' => 'mtn',
            'customer_target' => '08031234567',
            'status' => 'completed',
        ]);

        // Verify wallet debit
        $this->user->refresh();
        $expectedDebitUsd = 980.0 / 1500.0;
        $this->assertEqualsWithDelta($initialBalance - $expectedDebitUsd, (float) $this->user->walletBalance, 0.0001);
    }

    /**
     * Test successful data plan purchase flow.
     */
    public function test_successful_data_bundle_purchase(): void
    {
        $initialBalance = (float) $this->user->walletBalance;

        $response = $this->actingAs($this->user)->postJson(route('utilities.purchase.data'), [
            'network' => 'mtn',
            'phone' => '08039876543',
            'plan_id' => '102',
            'plan_name' => 'MTN 1.0GB SME Data',
            'amount_ngn' => 280.0,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('utility_transactions', [
            'user_id' => $this->user->id,
            'service_type' => 'data',
            'network_or_disco' => 'mtn',
            'customer_target' => '08039876543',
            'product_name' => 'MTN 1.0GB SME Data',
            'status' => 'completed',
        ]);

        $this->user->refresh();
        $expectedDebitUsd = 280.0 / 1500.0;
        $this->assertEqualsWithDelta($initialBalance - $expectedDebitUsd, (float) $this->user->walletBalance, 0.0001);
    }

    /**
     * Test electricity meter validation endpoint.
     */
    public function test_electricity_meter_verification(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('utilities.api.verify-meter'), [
            'disco' => 'ikedc',
            'meter_number' => '45028475923',
            'meter_type' => 'prepaid',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'customer_name',
            'name',
            'address',
            'meter_number',
            'disco',
            'meter_type',
        ]);
        $this->assertNotEmpty($response->json('customer_name'));
        $this->assertEquals($response->json('customer_name'), $response->json('name'));
    }

    /**
     * Test electricity meter resolves nested customer user name from live HTTP provider format.
     */
    public function test_electricity_meter_resolves_nested_customer_name_from_provider(): void
    {
        // Disable simulate mode to exercise HTTP response parser
        Setting::updateOrCreate(['key' => 'bigisub_simulate'], ['value' => '0']);

        \Illuminate\Support\Facades\Http::fake([
            'https://api.bigisub.ng/api/v2/bills/electricity/verify/' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'message' => 'Meter details verified successfully',
                'data' => [
                    'customer_name' => 'CHIEF OLUMIDE WILLIAMS',
                    'address' => 'Plot 10, Victoria Island, Lagos',
                    'meter_no' => '0101150041444',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('utilities.api.verify-meter'), [
            'disco' => 'ikeja-electric',
            'meter_number' => '0101150041444',
            'meter_type' => 'prepaid',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'customer_name' => 'CHIEF OLUMIDE WILLIAMS',
            'name' => 'CHIEF OLUMIDE WILLIAMS',
            'address' => 'Plot 10, Victoria Island, Lagos',
            'meter_number' => '0101150041444',
            'disco' => 'ikeja-electric',
            'meter_type' => 'prepaid',
        ]);
    }

    /**
     * Test electricity payment generates prepaid token.
     */
    public function test_electricity_prepaid_payment_and_token_generation(): void
    {
        $initialBalance = (float) $this->user->walletBalance;
        $amountNgn = 5000.0;

        $response = $this->actingAs($this->user)->postJson(route('utilities.pay.electricity'), [
            'disco' => 'ikedc',
            'meter_number' => '45028475923',
            'meter_type' => 'prepaid',
            'customer_name' => 'John Doe - Ikeja Electric',
            'amount' => $amountNgn,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'completed',
        ]);
        $response->assertJsonStructure([
            'token',
        ]);

        $tx = UtilityTransaction::where('user_id', $this->user->id)
            ->where('service_type', 'electricity')
            ->first();

        $this->assertNotNull($tx);
        $this->assertEquals('completed', $tx->status);
        $this->assertNotNull($tx->token);
        $this->assertEquals('ikeja-electric', $tx->network_or_disco);

        $this->user->refresh();
        $expectedDebitUsd = 5000.0 / 1500.0;
        $this->assertEqualsWithDelta($initialBalance - $expectedDebitUsd, (float) $this->user->walletBalance, 0.0001);
    }

    /**
     * Test betting customer ID verification endpoint.
     */
    public function test_betting_customer_verification(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('utilities.api.verify-betting'), [
            'platform' => 'sportybet',
            'customer_id' => '9847291',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'customer_name',
            'customer_id',
            'platform',
        ]);
    }

    /**
     * Test betting wallet funding flow.
     */
    public function test_betting_wallet_funding(): void
    {
        $initialBalance = (float) $this->user->walletBalance;
        $amountNgn = 2000.0;

        $response = $this->actingAs($this->user)->postJson(route('utilities.fund.betting'), [
            'platform' => 'sportybet',
            'customer_id' => '9847291',
            'customer_name' => 'Sporty Punter 9847291',
            'amount' => $amountNgn,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('utility_transactions', [
            'user_id' => $this->user->id,
            'service_type' => 'betting',
            'network_or_disco' => 'sportybet',
            'customer_target' => '9847291',
            'status' => 'completed',
        ]);

        $this->user->refresh();
        $expectedDebitUsd = 2000.0 / 1500.0;
        $this->assertEqualsWithDelta($initialBalance - $expectedDebitUsd, (float) $this->user->walletBalance, 0.0001);
    }

    /**
     * Test insufficient wallet balance rejection.
     */
    public function test_insufficient_wallet_balance_rejected(): void
    {
        // Bob has only $10 USD (15,000 NGN)
        $response = $this->actingAs($this->otherUser)->postJson(route('utilities.purchase.airtime'), [
            'network' => 'mtn',
            'phone' => '08031112233',
            'amount' => 50000.0, // ₦50,000 requires ~$33.33 USD
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        // Ensure no transaction was recorded as completed
        $this->assertDatabaseMissing('utility_transactions', [
            'user_id' => $this->otherUser->id,
            'status' => 'completed',
        ]);
    }

    /**
     * Test server validation for invalid phone or amount.
     */
    public function test_validation_errors_for_invalid_inputs(): void
    {
        // Negative amount
        $response = $this->actingAs($this->user)->postJson(route('utilities.purchase.airtime'), [
            'network' => 'mtn',
            'phone' => '08031234567',
            'amount' => -500,
        ]);
        $response->assertStatus(422);

        // Invalid network identifier
        $response2 = $this->actingAs($this->user)->postJson(route('utilities.purchase.airtime'), [
            'network' => 'invalid_network',
            'phone' => '08031234567',
            'amount' => 1000,
        ]);
        $response2->assertStatus(422);
    }

    /**
     * Test failure refund guarantee: When provider fails, wallet is atomically refunded.
     */
    public function test_automatic_refund_on_provider_failure(): void
    {
        $initialBalance = (float) $this->user->walletBalance;

        // Mock BigiSubService to return a definite failure response
        $mockService = $this->createMock(BigiSubService::class);
        $mockService->method('purchaseAirtime')->willReturn([
            'status' => 'failed',
            'message' => 'Provider error: Insufficient provider liquidity',
            'reference' => 'TEST-FAIL-01',
            'raw' => ['error' => 'API Error'],
        ]);

        $this->app->instance(BigiSubService::class, $mockService);

        $response = $this->actingAs($this->user)->postJson(route('utilities.purchase.airtime'), [
            'network' => 'airtel',
            'phone' => '08021234567',
            'amount' => 1000.0,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'status' => 'failed',
        ]);

        // Verify transaction is recorded as refunded
        $this->assertDatabaseHas('utility_transactions', [
            'user_id' => $this->user->id,
            'status' => 'refunded',
        ]);

        // User's wallet balance must be completely preserved/restored
        $this->user->refresh();
        $this->assertEqualsWithDelta($initialBalance, (float) $this->user->walletBalance, 0.0001);
    }

    /**
     * Test IDOR protection: User cannot view another user's receipt.
     */
    public function test_idor_protection_on_receipt(): void
    {
        // Create transaction belonging to Alice
        $tx = UtilityTransaction::create([
            'user_id' => $this->user->id,
            'reference' => 'UTL-AIR-ALICE-123',
            'service_type' => 'airtime',
            'provider' => 'bigisub',
            'network_or_disco' => 'mtn',
            'customer_target' => '08030000000',
            'amount_ngn' => 1000.0,
            'amount_usd' => 0.66666667,
            'total_charged_usd' => 0.65333333,
            'exchange_rate' => 1500.0,
            'status' => 'completed',
        ]);

        // Bob tries to access Alice's receipt
        $response = $this->actingAs($this->otherUser)->get(route('utilities.receipt', $tx->reference));
        $response->assertStatus(404);

        // Alice accesses her own receipt
        $aliceResponse = $this->actingAs($this->user)->get(route('utilities.receipt', $tx->reference));
        $aliceResponse->assertStatus(200);
        $aliceResponse->assertSee($tx->reference);
    }

    /**
     * Test admin can update BigiSub settings and configure live credentials.
     */
    public function test_admin_can_update_bigisub_settings(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.utilities.settings'), [
            'bigisub_api_token' => 'live_token_super_secret',
            'bigisub_pin' => '9988',
            'bigisub_base_url' => 'https://api.bigisub.ng',
            'bigisub_simulate' => '1',
            'utility_airtime_discount_percent' => '3.5',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'key' => 'bigisub_api_token',
            'value' => 'live_token_super_secret',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'bigisub_pin',
            'value' => '9988',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'utility_airtime_discount_percent',
            'value' => '3.5',
        ]);
    }

    /**
     * Test admin manual refund on uncertain/failed transaction.
     */
    public function test_admin_can_refund_failed_transaction(): void
    {
        $initialBalance = (float) $this->user->walletBalance;

        $tx = UtilityTransaction::create([
            'user_id' => $this->user->id,
            'reference' => 'UTL-ELC-PENDING-99',
            'service_type' => 'electricity',
            'provider' => 'bigisub',
            'network_or_disco' => 'ikedc',
            'customer_target' => '45028475923',
            'amount_ngn' => 3000.0,
            'amount_usd' => 2.0,
            'total_charged_usd' => 2.0,
            'exchange_rate' => 1500.0,
            'status' => 'uncertain',
        ]);

        // Non-admin is redirected by EnsureAdmin middleware
        $nonAdminResponse = $this->actingAs($this->otherUser)->post(route('admin.utilities.refund', $tx->id));
        $nonAdminResponse->assertRedirect('/dashboard');

        // Admin performs manual refund
        $adminResponse = $this->actingAs($this->admin)->post(route('admin.utilities.refund', $tx->id));
        $adminResponse->assertRedirect();
        $adminResponse->assertSessionHas('success');

        $tx->refresh();
        $this->assertEquals('refunded', $tx->status);
        $this->assertNotNull($tx->refunded_at);

        // User wallet should be credited with the 2.0 USD
        $this->user->refresh();
        $this->assertEqualsWithDelta($initialBalance + 2.0, (float) $this->user->walletBalance, 0.0001);
    }
}
