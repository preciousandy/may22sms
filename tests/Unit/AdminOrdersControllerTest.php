<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\OrdersController;
use App\Models\BoostOrder;
use App\Models\BoostService;
use App\Models\LogOrder;
use App\Models\SmsOrder;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminOrdersControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_refund_sms_order()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['walletBalance' => 0.00]);
        $this->actingAs($admin);

        $order = SmsOrder::create([
            'user_id' => $user->id,
            'provider' => 'daisysms',
            'order_id' => '9991',
            'phone_number' => '1234567890',
            'country' => 'usa',
            'operator' => 'any',
            'product' => 'whatsapp',
            'price' => 5.00,
            'status' => 'PENDING',
        ]);

        $controller = new OrdersController();
        $request = Request::create('/admin/orders/refund', 'POST', [
            'order_type' => 'sms',
            'order_id' => $order->id,
            'reason' => 'Test admin refund',
        ]);

        $response = $controller->refundOrder($request);

        $this->assertEquals('CANCELED', $order->fresh()->status);
        $this->assertEquals(5.00, (float) $user->fresh()->walletBalance);

        $refundTx = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'sms_refund')
            ->where('reference', 'SMSREFUND-' . $order->id)
            ->first();

        $this->assertNotNull($refundTx);
        $this->assertEquals(5.00, (float) $refundTx->amount_usd);
    }

    public function test_admin_can_refund_log_order()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['walletBalance' => 0.00]);
        $this->actingAs($admin);

        $order = LogOrder::create([
            'user_id' => $user->id,
            'total_items' => 2,
            'total_amount' => 12.50,
            'status' => 'pending',
        ]);

        $controller = new OrdersController();
        $request = Request::create('/admin/orders/refund', 'POST', [
            'order_type' => 'logs',
            'order_id' => $order->id,
            'reason' => 'Defective logs',
        ]);

        $response = $controller->refundOrder($request);

        $this->assertEquals('canceled', $order->fresh()->status);
        $this->assertEquals(12.50, (float) $user->fresh()->walletBalance);

        $refundTx = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'log_refund')
            ->where('reference', 'LOGREFUND-' . $order->id)
            ->first();

        $this->assertNotNull($refundTx);
        $this->assertEquals(12.50, (float) $refundTx->amount_usd);
    }

    public function test_admin_can_refund_boost_order()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['walletBalance' => 0.00]);
        $this->actingAs($admin);

        $service = BoostService::create([
            'jap_service_id' => '1',
            'service_id' => 1,
            'name' => 'Followers',
            'category' => 'Instagram',
            'rate' => 1.5,
            'min' => 10,
            'max' => 1000,
            'is_active' => true,
        ]);

        $order = BoostOrder::create([
            'user_id' => $user->id,
            'boost_service_id' => $service->id,
            'link' => 'https://example.com/profile',
            'quantity' => 1000,
            'charge' => 3.75,
            'status' => 'processing',
        ]);

        $controller = new OrdersController();
        $request = Request::create('/admin/orders/refund', 'POST', [
            'order_type' => 'boost',
            'order_id' => $order->id,
            'reason' => 'Undelivered boost',
        ]);

        $response = $controller->refundOrder($request);

        $this->assertEquals('canceled', $order->fresh()->status);
        $this->assertEquals(3.75, (float) $user->fresh()->walletBalance);

        $refundTx = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'boost_refund')
            ->where('reference', 'BOOSTREFUND-' . $order->id)
            ->first();

        $this->assertNotNull($refundTx);
        $this->assertEquals(3.75, (float) $refundTx->amount_usd);
    }
}
