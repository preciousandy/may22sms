<?php

namespace Tests\Unit;

use App\Services\HeroSmsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HeroSmsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.herosms.api_key' => 'testkey']);
    }

    public function test_buy_number_success()
    {
        Http::fake([
            'https://hero-sms.com/stubs/handler_api.php*' => Http::sequence()
                ->push('{"whatsapp":{"cost":0.6,"count":15}}', 200) // getPrices
                ->push('ACCESS_NUMBER:9001:19876543210', 200, ['X-Price' => '0.60']), // buyNumber
        ]);

        $service = new HeroSmsService();
        $result = $service->buyNumber('usa', 'any', 'whatsapp');

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('9001', $result['id']);
        $this->assertEquals('19876543210', $result['phone']);
        $this->assertEquals(0.60, $result['price']);
    }

    public function test_buy_number_returns_error_for_no_numbers()
    {
        Http::fake([
            'https://hero-sms.com/stubs/handler_api.php*' => Http::sequence()
                ->push('{"whatsapp":{"cost":0.6,"count":0}}', 200)
                ->push('NO_NUMBERS', 200),
        ]);

        $service = new HeroSmsService();
        $result = $service->buyNumber('usa', 'any', 'whatsapp');

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('No numbers available', $result['error']);
    }

    public function test_check_sms_receives_code()
    {
        Http::fake([
            'https://hero-sms.com/stubs/handler_api.php*' => Http::response('STATUS_OK:654321', 200),
        ]);

        $service = new HeroSmsService();
        $result = $service->checkSms('9001');

        $this->assertEquals('RECEIVED', $result['status']);
        $this->assertEquals('654321', $result['sms'][0]['code']);
    }

    public function test_check_sms_handles_canceled()
    {
        Http::fake([
            'https://hero-sms.com/stubs/handler_api.php*' => Http::response('STATUS_CANCEL', 200),
        ]);

        $service = new HeroSmsService();
        $result = $service->checkSms('9001');

        $this->assertEquals('CANCELED', $result['status']);
    }
}
