<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\OrdersController;
use App\Models\BoostOrder;
use App\Models\BoostService;
use App\Models\FundingTransaction;
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

    public function test_admin_can_cancel_unpaid_funding_without_refund()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['walletBalance' => 10.00]);
        $this->actingAs($admin);

        $tx = FundingTransaction::create([
            'user_id' => $user->id,
            'payment_method' => 'paymentpoint',
            'currency' => 'USD',
            'amount' => 15000,
            'amount_usd' => 10.00,
            'reference' => 'FUND-TEST-CANCEL-001',
            'status' => 'waiting_payment',
        ]);

        $controller = new OrdersController();
        $request = Request::create('/admin/orders/cancel', 'POST', [
            'order_type' => 'funding',
            'order_id' => $tx->id,
            'reason' => 'User never paid',
        ]);

        $response = $controller->cancelOrder($request);

        $this->assertEquals('canceled', $tx->fresh()->status);
        $this->assertEquals(10.00, (float) $user->fresh()->walletBalance);

        $refundExists = WalletTransaction::where('user_id', $user->id)->where('type', 'funding_credit')->exists();
        $this->assertFalse($refundExists);
    }

    public function test_admin_can_approve_pending_funding_and_credit_wallet()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['walletBalance' => 5.00]);
        $this->actingAs($admin);

        $tx = FundingTransaction::create([
            'user_id' => $user->id,
            'payment_method' => 'paymentpoint',
            'currency' => 'USD',
            'amount' => 30000,
            'amount_usd' => 20.00,
            'reference' => 'FUND-TEST-APPROVE-002',
            'status' => 'waiting_payment',
        ]);

        $controller = new OrdersController();
        $request = Request::create('/admin/orders/approve-funding', 'POST', [
            'order_id' => $tx->id,
        ]);

        $response = $controller->approveFunding($request);

        $this->assertEquals('approved', $tx->fresh()->status);
        $this->assertEquals(25.00, (float) $user->fresh()->walletBalance);

        $creditTx = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'funding_credit')
            ->where('reference', $tx->reference)
            ->first();

        $this->assertNotNull($creditTx);
        $this->assertEquals(20.00, (float) $creditTx->amount_usd);
    }

    public function test_admin_can_reverse_approved_funding_and_debit_wallet()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['walletBalance' => 30.00]);
        $this->actingAs($admin);

        $tx = FundingTransaction::create([
            'user_id' => $user->id,
            'payment_method' => 'pocketfi',
            'currency' => 'USD',
            'amount' => 15000,
            'amount_usd' => 10.00,
            'reference' => 'FUND-TEST-REV-003',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $controller = new OrdersController();
        $request = Request::create('/admin/orders/reverse-funding', 'POST', [
            'order_id' => $tx->id,
            'reason' => 'Fraudulent chargeback',
        ]);

        $response = $controller->reverseFunding($request);

        $this->assertEquals('rejected', $tx->fresh()->status);
        $this->assertEquals(20.00, (float) $user->fresh()->walletBalance);

        $revTx = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'funding_reversal')
            ->first();

        $this->assertNotNull($revTx);
        $this->assertEquals(-10.00, (float) $revTx->amount_usd);
    }

    public function test_admin_can_cancel_order_without_refund()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['walletBalance' => 15.00]);
        $this->actingAs($admin);

        $order = SmsOrder::create([
            'user_id' => $user->id,
            'provider' => 'daisysms',
            'order_id' => '9992',
            'phone_number' => '1234567891',
            'country' => 'usa',
            'operator' => 'any',
            'product' => 'telegram',
            'price' => 3.00,
            'status' => 'PENDING',
        ]);

        $controller = new OrdersController();
        $request = Request::create('/admin/orders/cancel', 'POST', [
            'order_type' => 'sms',
            'order_id' => $order->id,
            'reason' => 'User policy violation',
        ]);

        $response = $controller->cancelOrder($request);

        $this->assertEquals('CANCELED', $order->fresh()->status);
        $this->assertEquals(15.00, (float) $user->fresh()->walletBalance);

        $refundExists = WalletTransaction::where('user_id', $user->id)->where('type', 'sms_refund')->exists();
        $this->assertFalse($refundExists);
    }

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
