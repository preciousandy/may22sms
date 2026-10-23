<?php

namespace Tests\Unit;

use App\Models\FundingTransaction;
use App\Models\LogOrder;
use App\Models\SmsOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_with_total_spent_and_profit_metrics()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'account_status' => 'active',
        ]);
        $user = User::factory()->create([
            'account_status' => 'active',
        ]);
        $this->actingAs($admin);

        // Create completed SMS order
        SmsOrder::create([
            'user_id' => $user->id,
            'provider' => 'daisysms',
            'order_id' => 'SMS-TEST-1',
            'phone_number' => '+1234567890',
            'country' => 'usa',
            'operator' => 'any',
            'product' => 'telegram',
            'price' => 2.00,
            'status' => 'RECEIVED',
        ]);

        // Create completed Log order
        LogOrder::create([
            'user_id' => $user->id,
            'order_reference' => 'LOG-TEST-1',
            'total_amount' => 5.00,
            'total_items' => 1,
            'status' => 'completed',
        ]);

        // Create approved funding
        FundingTransaction::create([
            'user_id' => $user->id,
            'payment_method' => 'paymentpoint',
            'currency' => 'USD',
            'amount' => 15000,
            'amount_usd' => 10.00,
            'reference' => 'FUND-TEST-1',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('allTimeSpentUsd');
        $response->assertViewHas('totalAllTimeGain');
        $response->assertViewHas('allTimeFundingUsd');
        $response->assertViewHas('todaySpentUsd');
        $response->assertViewHas('todayTotalGain');

        // Total Spent should include SMS ($2.00) + Log ($5.00) = $7.00
        $spent = $response->viewData('allTimeSpentUsd');
        $this->assertEquals(7.00, $spent);

        // Funding should be $10.00
        $funding = $response->viewData('allTimeFundingUsd');
        $this->assertEquals(10.00, $funding);
    }

    public function test_admin_can_run_storage_link_maintenance()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'account_status' => 'active',
        ]);
        $this->actingAs($admin);

        $response = $this->post('/admin/settings/maintenance', [
            'action' => 'storage_link',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
