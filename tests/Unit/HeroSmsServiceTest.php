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

    public function test_get_operators_returns_country_operators()
    {
        \Illuminate\Support\Facades\Cache::forget('herosms:operators:usa');

        Http::fake([
            'https://hero-sms.com/stubs/handler_api.php*' => Http::response(
                '{"status":"success","countryOperators":{"187":["any","at_t","tmobile","verizon"]}}',
                200
            ),
        ]);

        $service = new HeroSmsService();
        $operators = $service->getOperators('usa');

        $this->assertEquals(['at_t', 'tmobile', 'verizon'], $operators);
    }

    public function test_buy_number_sends_selected_operator()
    {
        \Illuminate\Support\Facades\Cache::forget('herosms:prices:usa');

        Http::fake([
            'https://hero-sms.com/stubs/handler_api.php*' => Http::sequence()
                ->push('{"whatsapp":{"cost":2.6,"count":15}}', 200)
                ->push('ACCESS_NUMBER:9002:19876543211', 200, ['X-Price' => '2.80']),
        ]);

        $service = new HeroSmsService();
        $result = $service->buyNumber('usa', 'verizon', 'whatsapp');

        $this->assertEquals('9002', $result['id']);
        $this->assertEquals('verizon', $result['operator']);
        $this->assertEquals(2.80, $result['price']);

        Http::assertSent(function ($request) {
            return ($request['action'] ?? null) === 'getNumber'
                && ($request['operator'] ?? null) === 'verizon';
        });
    }

    public function test_get_price_tiers_parses_free_price_map()
    {
        \Illuminate\Support\Facades\Cache::forget('herosms:free_price:wa');

        Http::fake([
            'https://hero-sms.com/stubs/handler_api.php*' => Http::response(
                '{"0":{"country":36,"price":0.4,"retail_price":0.52,"count":1000,"freePriceMap":{"0.52":100,"0.81":500,"2.50":900,"5.00":1000}}}',
                200
            ),
        ]);

        $service = new HeroSmsService();
        $tiers = $service->getPriceTiers('canada', 'whatsapp');

        $this->assertCount(4, $tiers);
        $this->assertEquals(0.52, $tiers[0]['price']);
        $this->assertEquals(5.0, $tiers[3]['price']);
        $this->assertEquals(1000, $tiers[3]['count']);
    }

    public function test_buy_number_sends_max_price_when_provided()
    {
        \Illuminate\Support\Facades\Cache::forget('herosms:prices:canada');

        Http::fake([
            'https://hero-sms.com/stubs/handler_api.php*' => Http::sequence()
                ->push('{"whatsapp":{"cost":0.52,"count":100}}', 200)
                ->push('ACCESS_NUMBER:9003:14165550199', 200, ['X-Price' => '2.50']),
        ]);

        $service = new HeroSmsService();
        $result = $service->buyNumber('canada', 'verizon', 'whatsapp', 2.50);

        $this->assertEquals('9003', $result['id']);
        $this->assertEquals(2.50, $result['price']);

        Http::assertSent(function ($request) {
            return ($request['action'] ?? null) === 'getNumber'
                && ($request['operator'] ?? null) === 'verizon'
                && (float) ($request['maxPrice'] ?? 0) === 2.5;
        });
    }
}
