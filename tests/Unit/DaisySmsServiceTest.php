<?php

namespace Tests\Unit;

use App\Services\DaisySmsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DaisySmsServiceTest extends TestCase
{
    public function test_buy_number_success()
    {
        Http::fake([
            'https://daisysms.io/stubs/handler_api.php*' => Http::sequence()
                ->push('{"whatsapp":{"cost":0.5,"count":10}}', 200) // getPrices
                ->push('ACCESS_NUMBER:1001:1234567890', 200, ['X-Price' => '0.50']), // buyNumber
        ]);

        $service = new DaisySmsService();
        $result = $service->buyNumber('usa', 'any', 'whatsapp');

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('1001', $result['id']);
        $this->assertEquals('1234567890', $result['phone']);
        $this->assertEquals(0.5, $result['price']);
    }

    public function test_buy_number_returns_meaningful_error_when_provider_says_try_again()
    {
        Http::fake([
            'https://daisysms.io/stubs/handler_api.php*' => Http::sequence()
                ->push('{"whatsapp":{"cost":0.5,"count":10}}', 200) // getPrices
                ->push('Please try again later', 200), // buyNumber
        ]);

        $service = new DaisySmsService();
        $result = $service->buyNumber('usa', 'any', 'whatsapp');

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Please try again later', $result['error']);
        // Verify no requests were made to invalid backup domains
        Http::assertSentCount(2);
    }

    public function test_buy_number_returns_error_for_no_numbers()
    {
        Http::fake([
            'https://daisysms.io/stubs/handler_api.php*' => Http::sequence()
                ->push('{"whatsapp":{"cost":0.5,"count":10}}', 200)
                ->push('NO_NUMBERS', 200),
        ]);

        $service = new DaisySmsService();
        $result = $service->buyNumber('usa', 'any', 'whatsapp');

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('No numbers available', $result['error']);
    }

    public function test_check_sms_receives_code()
    {
        Http::fake([
            'https://daisysms.io/stubs/handler_api.php*' => Http::response('STATUS_OK:987654', 200),
        ]);

        $service = new DaisySmsService();
        $result = $service->checkSms('12345');

        $this->assertEquals('RECEIVED', $result['status']);
        $this->assertEquals('987654', $result['sms'][0]['code']);
    }

    public function test_check_sms_handles_no_activation()
    {
        Http::fake([
            'https://daisysms.io/stubs/handler_api.php*' => Http::response('NO_ACTIVATION', 200),
        ]);

        $service = new DaisySmsService();
        $result = $service->checkSms('12345');

        $this->assertEquals('CANCELED', $result['status']);
    }

    public function test_cloudflare_challenge_handled_gracefully()
    {
        $cfHtml = '<!DOCTYPE html><html><head><title>Just a moment...</title></head><body><script src="https://challenges.cloudflare.com"></script></body></html>';

        Http::fake([
            'https://daisysms.io/stubs/handler_api.php*' => Http::response($cfHtml, 403),
        ]);

        $service = new DaisySmsService();

        $pricesUsa = $service->getPrices('usa');
        $this->assertNotEmpty($pricesUsa);
        $this->assertArrayHasKey('usa', $pricesUsa);

        $pricesUnknown = $service->getPrices('unknown_country');
        $this->assertEquals([], $pricesUnknown);

        $balance = $service->getBalance();
        $this->assertArrayHasKey('error', $balance);
        $this->assertStringContainsString('Cloudflare', $balance['error']);

        $buy = $service->buyNumber('usa', 'any', 'whatsapp');
        $this->assertArrayHasKey('error', $buy);
        $this->assertStringContainsString('Cloudflare', $buy['error']);
    }

    public function test_flaresolverr_integration()
    {
        config(['services.daisysms.flaresolverr_url' => 'http://localhost:8191/v1']);

        Http::fake([
            'http://localhost:8191/v1' => Http::response([
                'status' => 'ok',
                'solution' => [
                    'response' => 'ACCESS_BALANCE:12.34',
                    'headers' => ['content-type' => 'text/plain'],
                    'status' => 200,
                ]
            ], 200),
        ]);

        $service = new DaisySmsService();
        $balance = $service->getBalance();

        $this->assertEquals(12.34, $balance['balance']);
    }
}
