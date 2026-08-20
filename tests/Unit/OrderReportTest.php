<?php

namespace Tests\Unit;

use App\Models\OrderReport;
use App\Models\SmsOrder;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_order_report_and_button_goes_out()
    {
        $user = User::factory()->create([
            'account_status' => 'active',
            'walletBalance' => 0.00,
        ]);
        $this->actingAs($user);

        $order = SmsOrder::create([
            'user_id' => $user->id,
            'provider' => 'daisysms',
            'order_id' => '99981',
            'phone_number' => '19998887777',
            'country' => 'usa',
            'operator' => 'any',
            'product' => 'telegram',
            'price' => 3.50,
            'status' => 'PENDING',
        ]);

        $this->assertFalse((bool) $order->is_reported);

        // User reports the order
        $response = $this->postJson('/orders/report', [
            'order_type' => 'sms',
            'order_id' => $order->id,
            'reason' => 'SMS code did not arrive within timer.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Order is now marked reported
        $this->assertTrue((bool) $order->fresh()->is_reported);

        // OrderReport entry created
        $report = OrderReport::where('user_id', $user->id)->where('order_id', $order->id)->first();
        $this->assertNotNull($report);
        $this->assertEquals('pending', $report->status);
        $this->assertEquals('SMS code did not arrive within timer.', $report->reason);

        // Submitting duplicate report fails
        $duplicateResponse = $this->postJson('/orders/report', [
            'order_type' => 'sms',
            'order_id' => $order->id,
            'reason' => 'Duplicate submission',
        ]);

        $duplicateResponse->assertStatus(422);
    }

    public function test_admin_can_resolve_and_refund_pending_order_report()
    {
        $admin = User::factory()->create([
            'account_status' => 'active',
            'is_admin' => true,
        ]);
        $user = User::factory()->create([
            'account_status' => 'active',
            'walletBalance' => 0.00,
        ]);

        $order = SmsOrder::create([
            'user_id' => $user->id,
            'provider' => 'daisysms',
            'order_id' => '99982',
            'phone_number' => '19998887778',
            'country' => 'usa',
            'operator' => 'any',
            'product' => 'whatsapp',
            'price' => 4.00,
            'status' => 'PENDING',
            'is_reported' => true,
            'reported_at' => now(),
            'report_status' => 'pending',
        ]);

        $report = OrderReport::create([
            'user_id' => $user->id,
            'order_type' => 'sms',
            'order_id' => $order->id,
            'reason' => 'Verification failed',
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->post("/admin/reports/{$report->id}/action", [
            'action' => 'refund',
            'admin_notes' => 'Full refund issued for failed SMS.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('resolved', $report->fresh()->status);
        $this->assertEquals('CANCELED', $order->fresh()->status);
        $this->assertEquals(4.00, (float) $user->fresh()->walletBalance);

        $refundTx = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'sms_refund')
            ->first();
        $this->assertNotNull($refundTx);
        $this->assertEquals(4.00, (float) $refundTx->amount_usd);
    }
}
