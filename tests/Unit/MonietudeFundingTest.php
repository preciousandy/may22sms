<?php

namespace Tests\Unit;

use App\Models\FundingTransaction;
use App\Models\Setting;
use App\Models\User;
use App\Services\MonietudeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MonietudeFundingTest extends TestCase
{
    use RefreshDatabase;

    public function test_format_phone_number_correctly()
    {
        $service = new MonietudeService();

        // 13 digits starting with 234
        $this->assertEquals('08012345678', $service->formatPhone('2348012345678'));

        // Formatted with +234
        $this->assertEquals('08012345678', $service->formatPhone('+2348012345678'));

        // 10 digits missing leading zero
        $this->assertEquals('08012345678', $service->formatPhone('8012345678'));

        // Already 11 digits
        $this->assertEquals('08012345678', $service->formatPhone('08012345678'));

        // Fallback for empty
        $this->assertEquals('08000000042', $service->formatPhone('', 42));
    }

    public function test_create_virtual_account_api_call()
    {
        Http::fake([
            'https://monietude.com/api/gateway/virtual-accounts' => Http::response([
                'status' => 'success',
                'message' => 'Virtual account created successfully',
                'data' => [
                    'account_number' => '6663154908',
                    'bank_name' => 'PalmPay',
                    'bank_code' => '000000',
                    'account_name' => 'M Test User',
                    'provider' => 'palmpay',
                    'phone' => '08012345678',
                ],
            ], 200),
        ]);

        $service = new MonietudeService();
        $result = $service->createVirtualAccount([
            'name' => 'Test User',
            'phone' => '08012345678',
            'email' => 'test@example.com',
            'provider' => 'palmpay',
        ]);

        $this->assertTrue($result['ok']);
        $this->assertEquals('6663154908', $result['data']['data']['account_number']);
        $this->assertEquals('PalmPay', $result['data']['data']['bank_name']);
    }

    public function test_webhook_successfully_funds_user_wallet()
    {
        Setting::updateOrCreate(['key' => 'ngn_per_usd'], ['value' => '1000']);
        Setting::updateOrCreate(['key' => 'funding_charge_enabled'], ['value' => '0']);

        $user = User::factory()->create([
            'name' => 'Monietude User',
            'email' => 'monietude@example.com',
            'walletBalance' => 10.00,
            'monietude_account_number' => '6663154908',
            'monietude_bank_name' => 'PalmPay',
            'monietude_account_name' => 'M Monietude User',
            'monietude_provider' => 'palmpay',
        ]);

        Http::fake([
            'https://monietude.com/api/gateway/payments/verify/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'success',
                    'amount' => 500000,
                ],
            ], 200),
        ]);

        // 500000 kobo = 5000 NGN. At 1000 NGN/USD = 5.00 USD
        $webhookPayload = [
            'event' => 'virtual_account.funding.success',
            'type' => 'virtual_account_funding',
            'reference' => 'fund_test_ref_12345',
            'status' => 'success',
            'amount' => 500000,
            'currency' => 'NGN',
            'virtual_account' => [
                'account_number' => '6663154908',
                'account_name' => 'M Monietude User',
                'bank_name' => 'PalmPay',
                'customer_phone' => '08012345678',
                'customer_email' => 'monietude@example.com',
            ],
        ];

        $response = $this->postJson('/api/monietude/webhook', $webhookPayload);

        $response->assertStatus(200)
            ->assertJson(['ok' => true]);

        $user->refresh();
        $this->assertEquals(15.00, (float) $user->walletBalance);

        $tx = FundingTransaction::where('reference', 'fund_test_ref_12345')->first();
        $this->assertNotNull($tx);
        $this->assertEquals('approved', $tx->status);
        $this->assertEquals(5000.00, (float) $tx->amount);
        $this->assertEquals(5.00, (float) $tx->amount_usd);
    }

    public function test_webhook_is_idempotent()
    {
        Setting::updateOrCreate(['key' => 'ngn_per_usd'], ['value' => '1000']);
        Setting::updateOrCreate(['key' => 'funding_charge_enabled'], ['value' => '0']);

        $user = User::factory()->create([
            'walletBalance' => 10.00,
            'monietude_account_number' => '6663154908',
        ]);

        Http::fake([
            'https://monietude.com/api/gateway/payments/verify/*' => Http::response([
                'status' => 'success',
                'data' => ['status' => 'success'],
            ], 200),
        ]);

        $webhookPayload = [
            'event' => 'virtual_account.funding.success',
            'type' => 'virtual_account_funding',
            'reference' => 'fund_idempotent_ref',
            'status' => 'success',
            'amount' => 500000, // 5000 NGN -> 5.00 USD
            'virtual_account' => [
                'account_number' => '6663154908',
            ],
        ];

        // First call
        $this->postJson('/api/monietude/webhook', $webhookPayload)->assertStatus(200);
        $user->refresh();
        $this->assertEquals(15.00, (float) $user->walletBalance);

        // Second call with same reference
        $this->postJson('/api/monietude/webhook', $webhookPayload)->assertStatus(200);
        $user->refresh();
        $this->assertEquals(15.00, (float) $user->walletBalance); // Balance must NOT increase again
    }

    public function test_failed_webhook_does_not_credit_wallet()
    {
        $user = User::factory()->create([
            'walletBalance' => 10.00,
            'monietude_account_number' => '6663154908',
        ]);

        Http::fake([
            'https://monietude.com/api/gateway/payments/verify/*' => Http::response([
                'status' => 'failed',
            ], 200),
        ]);

        $webhookPayload = [
            'event' => 'payment.failed',
            'reference' => 'fund_failed_ref',
            'status' => 'failed',
            'amount' => 500000,
            'virtual_account' => [
                'account_number' => '6663154908',
            ],
        ];

        $this->postJson('/api/monietude/webhook', $webhookPayload)->assertStatus(200);
        $user->refresh();
        $this->assertEquals(10.00, (float) $user->walletBalance);
    }
}
