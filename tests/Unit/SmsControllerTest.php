<?php

namespace Tests\Unit;

use App\Http\Controllers\User\SmsController;
use App\Models\SmsOrder;
use App\Models\User;
use App\Services\DaisySmsService;
use App\Services\FiveSimService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SmsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancel_order_allowed_after_2_minutes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = SmsOrder::create([
            'user_id' => $user->id,
            'provider' => 'daisysms',
            'order_id' => '12345',
            'phone_number' => '1234567890',
            'country' => 'usa',
            'operator' => 'any',
            'product' => 'whatsapp',
            'price' => 1.00,
            'status' => 'PENDING',
        ]);
        SmsOrder::where('id', $order->id)->update(['created_at' => now()->subMinutes(3)]);
        $order->refresh();

        $fiveSimMock = $this->createMock(FiveSimService::class);
        $daisySmsMock = $this->createMock(DaisySmsService::class);
        $heroSmsMock = $this->createMock(\App\Services\HeroSmsService::class);
        $daisySmsMock->expects($this->once())->method('cancelOrder')->with('12345')->willReturn(['success' => true]);

        $controller = new SmsController($fiveSimMock, $daisySmsMock, $heroSmsMock);

        $request = Request::create("/api/sms/cancel/{$order->id}", 'POST');
        $request->headers->set('Accept', 'application/json');

        $response = $controller->cancelOrder($request, $order->id);

        $responseData = $response->getData(true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('CANCELED', $responseData['status']);
        $this->assertEquals('CANCELED', $order->fresh()->status);
    }

    public function test_cancel_order_rejected_before_2_minutes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = SmsOrder::create([
            'user_id' => $user->id,
            'provider' => 'daisysms',
            'order_id' => '12346',
            'phone_number' => '1234567891',
            'country' => 'usa',
            'operator' => 'any',
            'product' => 'whatsapp',
            'price' => 1.00,
            'status' => 'PENDING',
            'created_at' => now()->subSeconds(30),
        ]);

        $fiveSimMock = $this->createMock(FiveSimService::class);
        $daisySmsMock = $this->createMock(DaisySmsService::class);
        $heroSmsMock = $this->createMock(\App\Services\HeroSmsService::class);

        $controller = new SmsController($fiveSimMock, $daisySmsMock, $heroSmsMock);

        $request = Request::create("/api/sms/cancel/{$order->id}", 'POST');
        $request->headers->set('Accept', 'application/json');

        $response = $controller->cancelOrder($request, $order->id);

        $responseData = $response->getData(true);
        $this->assertFalse($responseData['success']);
        $this->assertStringContainsString('Please wait', $responseData['message']);
        $this->assertEquals('PENDING', $order->fresh()->status);
    }
}
