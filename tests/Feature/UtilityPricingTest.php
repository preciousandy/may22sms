<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Models\UtilityPricingAuditLog;
use App\Models\UtilityTransaction;
use App\Services\BigiSubService;
use App\Services\UtilityPricingService;
use App\Services\UtilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UtilityPricingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected UtilityPricingService $pricingService;
    protected UtilityService $utilityService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pricingService = app(UtilityPricingService::class);
        $this->utilityService = app(UtilityService::class);

        // Standard test exchange rate: 1 USD = 1,500 NGN
        Setting::updateOrCreate(['key' => 'ngn_per_usd'], ['value' => '1500']);

        // BigiSub simulation mode
        Setting::updateOrCreate(['key' => 'bigisub_api_token'], ['value' => 'test_token_live']);
        Setting::updateOrCreate(['key' => 'bigisub_pin'], ['value' => '1234']);
        Setting::updateOrCreate(['key' => 'bigisub_base_url'], ['value' => 'https://api.bigisub.ng']);
        Setting::updateOrCreate(['key' => 'bigisub_simulate'], ['value' => '1']);

        // Default recommended initial pricing settings
        Setting::updateOrCreate(['key' => 'utility_data_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'utility_data_pricing_mode'], ['value' => 'fixed']);
        Setting::updateOrCreate(['key' => 'utility_data_markup_value'], ['value' => '40.0']);
        Setting::updateOrCreate(['key' => 'utility_data_min_amount'], ['value' => '50.0']);
        Setting::updateOrCreate(['key' => 'utility_data_max_amount'], ['value' => '100000.0']);

        Setting::updateOrCreate(['key' => 'utility_airtime_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'utility_airtime_discount_percent'], ['value' => '0.0']);
        Setting::updateOrCreate(['key' => 'utility_airtime_fee_mode'], ['value' => 'none']);
        Setting::updateOrCreate(['key' => 'utility_airtime_fee_value'], ['value' => '0.0']);
        Setting::updateOrCreate(['key' => 'utility_airtime_provider_discount_percent'], ['value' => '2.0']);
        Setting::updateOrCreate(['key' => 'utility_airtime_min_amount'], ['value' => '50.0']);
        Setting::updateOrCreate(['key' => 'utility_airtime_max_amount'], ['value' => '50000.0']);
        Setting::updateOrCreate(['key' => 'utility_allow_negative_profit'], ['value' => '0']);

        Setting::updateOrCreate(['key' => 'utility_electricity_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'utility_electricity_fee_mode'], ['value' => 'fixed']);
        Setting::updateOrCreate(['key' => 'utility_electricity_fee_value'], ['value' => '100.0']);
        Setting::updateOrCreate(['key' => 'utility_electricity_min_amount'], ['value' => '500.0']);
        Setting::updateOrCreate(['key' => 'utility_electricity_max_amount'], ['value' => '500000.0']);

        Setting::updateOrCreate(['key' => 'utility_betting_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'utility_betting_fee_mode'], ['value' => 'percent']);
        Setting::updateOrCreate(['key' => 'utility_betting_fee_value'], ['value' => '1.5']);
        Setting::updateOrCreate(['key' => 'utility_betting_min_amount'], ['value' => '100.0']);
        Setting::updateOrCreate(['key' => 'utility_betting_max_amount'], ['value' => '500000.0']);

        // Create standard user with $100 USD (₦150,000 NGN)
        $this->user = User::factory()->create([
            'name' => 'Jane Tech',
            'email' => 'jane.tech@example.com',
            'walletBalance' => 100.00000000,
            'account_status' => 'active',
            'is_admin' => false,
        ]);

        // Create admin user
        $this->admin = User::factory()->create([
            'name' => 'Chief Admin',
            'email' => 'admin.chief@example.com',
            'walletBalance' => 500.00000000,
            'account_status' => 'active',
            'is_admin' => true,
        ]);
    }

    /**
     * 1. Test Fixed Data Markup calculation and transaction recording.
     */
    public function test_fixed_data_markup_calculation_and_purchase(): void
    {
        // ₦40 fixed markup configured in setUp
        $rawPlans = app(BigiSubService::class)->getRawDataPlans('mtn');
        $this->assertNotEmpty($rawPlans);
        $samplePlan = $rawPlans[0];
        $samplePlanId = $samplePlan['id'] ?? $samplePlan['plan_id'];
        $wholesalePrice = (float) ($samplePlan['price_ngn'] ?? ($samplePlan['plan_amount'] ?? 0));
        $expectedSellingPrice = round($wholesalePrice + 40.0, 2);
        $expectedProfitNgn = 40.00;

        // Verify catalog shows marked-up selling price
        $catalogPlans = app(BigiSubService::class)->getDataPlans('mtn');
        $markedPlan = collect($catalogPlans)->first(function ($item) use ($samplePlanId) {
            return ($item['id'] ?? ($item['plan_id'] ?? null)) == $samplePlanId;
        });
        $this->assertNotNull($markedPlan);
        $this->assertEquals($expectedSellingPrice, (float) $markedPlan['price_ngn']);
        $this->assertEquals($wholesalePrice, (float) $markedPlan['wholesale_price_ngn']);

        // Execute purchase through controller
        $rate = 1500.0;
        $expectedDebitUsd = round($expectedSellingPrice / $rate, 8);
        $initialBalance = (float) $this->user->walletBalance;

        $response = $this->actingAs($this->user)->post(route('utilities.purchase.data'), [
            'network' => 'mtn',
            'phone_number' => '08031234567',
            'plan_id' => $samplePlanId,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check wallet debit
        $this->user->refresh();
        $this->assertEqualsWithDelta($initialBalance - $expectedDebitUsd, (float) $this->user->walletBalance, 0.0001);

        // Check transaction record
        $tx = UtilityTransaction::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($tx);
        $this->assertEquals('data', $tx->service_type);
        $this->assertEquals($wholesalePrice, (float) $tx->provider_cost_ngn);
        $this->assertEquals($expectedSellingPrice, (float) $tx->selling_price_ngn);
        $this->assertEquals($expectedProfitNgn, (float) $tx->profit_ngn);
        $this->assertEqualsWithDelta(round($expectedProfitNgn / $rate, 8), (float) $tx->profit_usd, 0.0001);

        // Check immutable pricing snapshot
        $this->assertIsArray($tx->pricing_snapshot);
        $this->assertEquals('fixed', $tx->pricing_snapshot['pricing_mode']);
        $this->assertEquals(40.0, (float) $tx->pricing_snapshot['markup_ngn']);
        $this->assertEquals($wholesalePrice, (float) $tx->pricing_snapshot['wholesale_ngn']);
    }

    /**
     * 2. Test Percentage Data Markup calculation.
     */
    public function test_percentage_data_markup_calculation(): void
    {
        Setting::updateOrCreate(['key' => 'utility_data_pricing_mode'], ['value' => 'percent']);
        Setting::updateOrCreate(['key' => 'utility_data_markup_value'], ['value' => '10.0']);

        $wholesale = 300.00;
        $pricing = $this->pricingService->calculateDataPricing($wholesale, 'mtn');

        $this->assertEquals(300.00, $pricing['wholesale_cost_ngn']);
        $this->assertEquals(30.00, $pricing['markup_ngn']);
        $this->assertEquals(330.00, $pricing['selling_price_ngn']);
        $this->assertEquals(30.00, $pricing['profit_ngn']);
    }

    /**
     * 3. Test Airtime with 0% discount and wholesale provider cost profit.
     */
    public function test_airtime_zero_discount_and_provider_margin(): void
    {
        // 0% user discount, 2% provider wholesale discount
        Setting::updateOrCreate(['key' => 'utility_airtime_discount_percent'], ['value' => '0.00']);
        Setting::updateOrCreate(['key' => 'utility_airtime_provider_discount_percent'], ['value' => '2.00']);

        $faceValue = 1000.00;
        $rate = 1500.0;
        $initialBalance = (float) $this->user->walletBalance;

        $response = $this->actingAs($this->user)->post(route('utilities.purchase.airtime'), [
            'network' => 'mtn',
            'phone_number' => '08031234567',
            'amount' => $faceValue,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $tx = UtilityTransaction::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($tx);
        $this->assertEquals(1000.00, (float) $tx->amount_ngn);
        $this->assertEquals(0.00, (float) $tx->discount_ngn);
        $this->assertEquals(1000.00, (float) $tx->selling_price_ngn);
        // Provider wholesale cost: 1000 - 2% = 980
        $this->assertEquals(980.00, (float) $tx->provider_cost_ngn);
        // Platform gross profit: 1000 - 980 = 20 NGN
        $this->assertEquals(20.00, (float) $tx->profit_ngn);
        $this->assertEqualsWithDelta(round(20.00 / $rate, 8), (float) $tx->profit_usd, 0.0001);

        // Verify request payload sent to provider has face value of 1000
        $this->assertEquals(1000.0, (float) $tx->request_payload['amount']);
    }

    /**
     * 4. Test Airtime with configurable discount.
     */
    public function test_airtime_with_configurable_discount(): void
    {
        // 1% user discount, 2% provider wholesale discount
        Setting::updateOrCreate(['key' => 'utility_airtime_discount_percent'], ['value' => '1.00']);
        Setting::updateOrCreate(['key' => 'utility_airtime_provider_discount_percent'], ['value' => '2.00']);

        $pricing = $this->pricingService->calculateAirtimePricing(1000.00, 'airtel');

        $this->assertEquals(1000.00, $pricing['face_value_ngn']);
        $this->assertEquals(10.00, $pricing['discount_ngn']);
        $this->assertEquals(990.00, $pricing['total_customer_ngn']);
        $this->assertEquals(980.00, $pricing['wholesale_cost_ngn']);
        $this->assertEquals(10.00, $pricing['profit_ngn']);
    }

    /**
     * 5. Test Negative Profit Guard on Airtime.
     */
    public function test_airtime_negative_profit_guard_blocks_unauthorized_loss(): void
    {
        // User discount 5% > provider discount 2% -> would result in -3% loss
        Setting::updateOrCreate(['key' => 'utility_airtime_discount_percent'], ['value' => '5.00']);
        Setting::updateOrCreate(['key' => 'utility_airtime_provider_discount_percent'], ['value' => '2.00']);
        Setting::updateOrCreate(['key' => 'utility_allow_negative_profit'], ['value' => '0']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/negative profit/i');

        $this->pricingService->calculateAirtimePricing(1000.00, 'mtn');
    }

    /**
     * 6. Test Electricity Fixed Convenience Fee and Provider Amount Separation.
     */
    public function test_electricity_fixed_convenience_fee_and_amount_separation(): void
    {
        Setting::updateOrCreate(['key' => 'utility_electricity_fee_mode'], ['value' => 'fixed']);
        Setting::updateOrCreate(['key' => 'utility_electricity_fee_value'], ['value' => '100.00']);

        $meterRechargeAmount = 5000.00;
        $rate = 1500.0;
        $expectedTotalDebitNgn = 5100.00; // 5000 + 100 fee
        $expectedDebitUsd = round($expectedTotalDebitNgn / $rate, 8);
        $initialBalance = (float) $this->user->walletBalance;

        $response = $this->actingAs($this->user)->post(route('utilities.pay.electricity'), [
            'disco' => 'ikeja-electric',
            'meter_number' => '11223344556',
            'meter_type' => 'prepaid',
            'amount' => $meterRechargeAmount,
            'customer_name' => 'John Meter Doe',
            'phone_number' => '08031234567',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check wallet debit includes the fee
        $this->user->refresh();
        $this->assertEqualsWithDelta($initialBalance - $expectedDebitUsd, (float) $this->user->walletBalance, 0.0001);

        // Check transaction record
        $tx = UtilityTransaction::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($tx);
        $this->assertEquals(5000.00, (float) $tx->amount_ngn); // Face value to provider
        $this->assertEquals(100.00, (float) $tx->fee_ngn); // Convenience fee
        $this->assertEquals(5100.00, (float) $tx->selling_price_ngn); // Total charged to customer
        $this->assertEquals(5000.00, (float) $tx->provider_cost_ngn); // Provider gets face value
        $this->assertEquals(100.00, (float) $tx->profit_ngn); // Profit is the fee
        $this->assertEqualsWithDelta(round(100.00 / $rate, 8), (float) $tx->profit_usd, 0.0001);

        // CRITICAL: Ensure BigiSub was sent ONLY the face value (₦5,000), not the platform fee
        $this->assertEquals(5000.00, (float) $tx->request_payload['amount']);
    }

    /**
     * 7. Test Betting Percentage Processing Fee and Fee Separation.
     */
    public function test_betting_percentage_processing_fee_and_fee_separation(): void
    {
        Setting::updateOrCreate(['key' => 'utility_betting_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'utility_betting_fee_mode'], ['value' => 'percent']);
        Setting::updateOrCreate(['key' => 'utility_betting_fee_value'], ['value' => '1.5']);

        $depositAmount = 2000.00;
        $expectedFeeNgn = 30.00; // 1.5% of 2000
        $expectedTotalDebitNgn = 2030.00;
        $rate = 1500.0;
        $expectedDebitUsd = round($expectedTotalDebitNgn / $rate, 8);
        $initialBalance = (float) $this->user->walletBalance;

        $response = $this->actingAs($this->user)->post(route('utilities.fund.betting'), [
            'platform' => 'sportybet',
            'customer_id' => '10293847',
            'customer_name' => 'Bettor Ace',
            'amount' => $depositAmount,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check wallet debit
        $this->user->refresh();
        $this->assertEqualsWithDelta($initialBalance - $expectedDebitUsd, (float) $this->user->walletBalance, 0.0001);

        // Check transaction details
        $tx = UtilityTransaction::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($tx);
        $this->assertEquals(2000.00, (float) $tx->amount_ngn); // Provider funding amount
        $this->assertEquals(30.00, (float) $tx->fee_ngn); // 1.5% fee
        $this->assertEquals(2030.00, (float) $tx->selling_price_ngn); // Customer charge
        $this->assertEquals(2000.00, (float) $tx->provider_cost_ngn); // Provider settlement
        $this->assertEquals(30.00, (float) $tx->profit_ngn); // Profit
        $this->assertEqualsWithDelta(round(30.00 / $rate, 8), (float) $tx->profit_usd, 0.0001);

        // CRITICAL: Ensure BigiSub was sent ONLY the requested funding amount (₦2,000)
        $this->assertEquals(2000.00, (float) $tx->request_payload['amount']);
    }

    /**
     * 8. Test Betting service disabled by default prevents transaction.
     */
    public function test_betting_service_disabled_blocks_purchase(): void
    {
        Setting::updateOrCreate(['key' => 'utility_betting_enabled'], ['value' => '0']);

        $response = $this->actingAs($this->user)->post(route('utilities.fund.betting'), [
            'platform' => 'sportybet',
            'customer_id' => '10293847',
            'customer_name' => 'Bettor Ace',
            'amount' => 2000.00,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('disabled', session('error'));
    }

    /**
     * 9. Test Insufficient wallet balance rejection.
     */
    public function test_insufficient_wallet_balance_blocks_transaction(): void
    {
        // Reduce user wallet to $0.50 USD (₦750)
        $this->user->update(['walletBalance' => 0.50]);

        $response = $this->actingAs($this->user)->post(route('utilities.pay.electricity'), [
            'disco' => 'ikeja-electric',
            'meter_number' => '11223344556',
            'meter_type' => 'prepaid',
            'amount' => 5000.00, // Requires ~$3.40 USD
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Insufficient wallet balance', session('error'));
    }

    /**
     * 10. Test Server-side Quote API endpoint.
     */
    public function test_server_side_quote_api(): void
    {
        // Electricity quote
        $response = $this->actingAs($this->user)->getJson(route('utilities.api.quote', [
            'service' => 'electricity',
            'amount' => 5000,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'quote' => [
                'face_value_ngn' => 5000.0,
                'fee_ngn' => 100.0,
                'total_customer_ngn' => 5100.0,
            ],
        ]);

        // Betting quote with 1.5% fee
        $response = $this->actingAs($this->user)->getJson(route('utilities.api.quote', [
            'service' => 'betting',
            'amount' => 2000,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'quote' => [
                'face_value_ngn' => 2000.0,
                'fee_ngn' => 30.0,
                'total_customer_ngn' => 2030.0,
            ],
        ]);
    }

    /**
     * 11. Test Failed Transaction Refund reverses wallet debit and does not realize profit.
     */
    public function test_refund_restores_wallet_and_marks_transaction(): void
    {
        // Create an uncertain/failed transaction directly
        $rate = 1500.0;
        $chargedUsd = round(5100.00 / $rate, 8);
        $initialBalance = (float) $this->user->walletBalance;

        // Debit wallet
        $this->user->decrement('walletBalance', $chargedUsd);

        $tx = UtilityTransaction::create([
            'user_id' => $this->user->id,
            'service_type' => 'electricity',
            'reference' => 'TEST-REF-' . uniqid(),
            'idempotency_key' => 'IDEM-' . uniqid(),
            'network_or_disco' => 'ikeja-electric',
            'amount_ngn' => 5000.00,
            'amount_usd' => round(5000.00 / $rate, 8),
            'fee_ngn' => 100.00,
            'discount_ngn' => 0.00,
            'selling_price_ngn' => 5100.00,
            'provider_cost_ngn' => 5000.00,
            'profit_ngn' => 100.00,
            'profit_usd' => round(100.00 / $rate, 8),
            'exchange_rate' => $rate,
            'total_charged_usd' => $chargedUsd,
            'provider' => 'bigisub',
            'customer_target' => '11223344556',
            'status' => 'failed',
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertTrue($tx->canRefund());

        // Perform refund via UtilityService
        $refunded = $this->utilityService->refundTransaction($tx, null, 'Provider rejected meter');
        $this->assertEquals('refunded', $refunded->status);

        $this->user->refresh();
        $tx->refresh();

        // Wallet balance fully restored
        $this->assertEqualsWithDelta($initialBalance, (float) $this->user->walletBalance, 0.0001);
        $this->assertEquals('refunded', $tx->status);
        $this->assertNotNull($tx->refunded_at);

        // Cannot double refund
        $this->assertFalse($tx->canRefund());
        $this->utilityService->refundTransaction($tx, null, 'Double refund attempt');
        $this->user->refresh();
        $this->assertEqualsWithDelta($initialBalance, (float) $this->user->walletBalance, 0.0001);
    }

    /**
     * 12. Test Admin Pricing Configuration Update with Audit Logging.
     */
    public function test_admin_pricing_update_records_audit_log(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.utilities.settings'), [
            'bigisub_api_token' => 'new_token_789',
            'bigisub_pin' => '9999',
            'bigisub_base_url' => 'https://api.bigisub.ng',
            'bigisub_simulate' => '1',
            'utility_data_enabled' => '1',
            'utility_data_pricing_mode' => 'fixed',
            'utility_data_markup_value' => '55.00',
            'utility_data_min_amount' => '50.00',
            'utility_data_max_amount' => '100000.00',
            'utility_airtime_enabled' => '1',
            'utility_airtime_discount_percent' => '0.00',
            'utility_airtime_fee_mode' => 'none',
            'utility_airtime_fee_value' => '0.00',
            'utility_airtime_provider_discount_percent' => '2.50',
            'utility_airtime_min_amount' => '50.00',
            'utility_airtime_max_amount' => '50000.00',
            'utility_electricity_enabled' => '1',
            'utility_electricity_fee_mode' => 'fixed',
            'utility_electricity_fee_value' => '150.00',
            'utility_electricity_min_amount' => '500.00',
            'utility_electricity_max_amount' => '500000.00',
            'utility_betting_enabled' => '0',
            'utility_betting_fee_mode' => 'percent',
            'utility_betting_fee_value' => '2.00',
            'utility_betting_min_amount' => '100.00',
            'utility_betting_max_amount' => '500000.00',
            'utility_allow_negative_profit' => '0',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check settings updated in database
        $this->assertEquals('55.00', Setting::where('key', 'utility_data_markup_value')->value('value'));
        $this->assertEquals('150.00', Setting::where('key', 'utility_electricity_fee_value')->value('value'));

        // Check audit log created
        $logs = UtilityPricingAuditLog::where('admin_id', $this->admin->id)->get();
        $this->assertNotEmpty($logs);
        $dataMarkupLog = $logs->firstWhere('setting_key', 'utility_data_markup_value');
        $this->assertNotNull($dataMarkupLog);
        $this->assertEquals('40.0', $dataMarkupLog->previous_value);
        $this->assertEquals('55.00', $dataMarkupLog->new_value);
    }

    /**
     * 13. Test Non-Admin cannot access Admin pricing settings.
     */
    public function test_non_admin_cannot_update_pricing_settings(): void
    {
        $response = $this->actingAs($this->user)->post(route('admin.utilities.settings'), [
            'utility_data_markup_value' => '1000.00',
        ]);

        // Forbidden or redirected
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
        $this->assertNotEquals('1000.00', Setting::where('key', 'utility_data_markup_value')->value('value'));
    }
}
