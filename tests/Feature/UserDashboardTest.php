<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_opay_styled_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Michael Scott',
            'email' => 'michael@example.com',
            'walletBalance' => 150.00,
            'account_status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Available Balance');
        $response->assertSee('Quick Services');
        $response->assertSee('SMS Verify');
        $response->assertSee('Logs Store');
        $response->assertSee('Social Boost');
        $response->assertSee('Recent Transactions');
        $response->assertSee('Receive Crypto');
        $response->assertSee('Airtime & Data');
        $response->assertSee('Electricity');
        $response->assertSee('NEW');
    }

    public function test_unauthenticated_user_is_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_redesigned_logs_store(): void
    {
        $user = User::factory()->create([
            'name' => 'Jim Halpert',
            'email' => 'jim@example.com',
            'walletBalance' => 50.00,
            'account_status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/logs-store');

        $response->assertStatus(200);
        $response->assertSee('Social Media Logs Store');
        $response->assertSee('Digital Logs & Social Accounts');
        $response->assertSee('Logs Catalog');
        $response->assertSee('My Purchased Logs');
    }

    public function test_authenticated_user_can_view_redesigned_social_boosting(): void
    {
        $user = User::factory()->create([
            'name' => 'Pam Beesly',
            'email' => 'pam@example.com',
            'walletBalance' => 80.00,
            'account_status' => 'active',
        ]);

        \App\Models\BoostService::create([
            'jap_service_id' => 10,
            'name' => 'Instagram Real Followers',
            'category' => 'Instagram',
            'rate' => 1.50,
            'min' => 10,
            'max' => 10000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/social-boosting');

        $response->assertStatus(200);
        $response->assertSee('Social Boosting');
        $response->assertSee('Social Media Engagement & Boosting');
        $response->assertSee('Target Link or Username');
        $response->assertSee('Order Quantity');
        $response->assertSee('Live Boost Orders Feed');
    }

    public function test_authenticated_user_can_view_coming_soon_service_pages(): void
    {
        $user = User::factory()->create([
            'name' => 'Dwight Schrute',
            'email' => 'dwight@example.com',
            'account_status' => 'active',
        ]);

        // 1. Receive Crypto & Convert to Naira (Live Quidax Off-Ramp)
        $resCrypto = $this->actingAs($user)->get('/services/crypto-to-naira');
        $resCrypto->assertStatus(200);
        $resCrypto->assertSee('Receive Crypto & Convert to Naira');
        $resCrypto->assertSee('Select Cryptocurrency');

        // 2. Airtime & Data (Live BigiSub Utility)
        $resAirtime = $this->actingAs($user)->get('/services/airtime-data');
        $resAirtime->assertStatus(200);
        $resAirtime->assertSee('Airtime');
        $resAirtime->assertSee('Data Bundles');

        // 3. Electricity (Live BigiSub Utility)
        $resPower = $this->actingAs($user)->get('/services/electricity');
        $resPower->assertStatus(200);
        $resPower->assertSee('Electricity');

        // 4. Betting (Live BigiSub Utility)
        $resBetting = $this->actingAs($user)->get('/services/betting');
        $resBetting->assertStatus(200);
        $resBetting->assertSee('Betting');
    }

    public function test_authenticated_user_can_view_tutorials_and_service_guides(): void
    {
        $user = User::factory()->create([
            'name' => 'Michael Scott',
            'email' => 'michael@example.com',
            'account_status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/tutorials');

        $response->assertStatus(200);
        $response->assertSee('How to Use Every May22SMS Service');
        $response->assertSee('SMS Verifications');
        $response->assertSee('Logs &amp; Accounts', false);
        $response->assertSee('Social Boosting');
        $response->assertSee('Add Money / Funding');
        $response->assertSee('Auto-Refund Protection');

        // Test alias route /how-it-works
        $aliasResponse = $this->actingAs($user)->get('/how-it-works');
        $aliasResponse->assertStatus(200);
        $aliasResponse->assertSee('How to Use Every May22SMS Service');

        // Test deep-link tab parameter
        $tabResponse = $this->actingAs($user)->get('/tutorials?tab=logs');
        $tabResponse->assertStatus(200);
        $tabResponse->assertSee('How to Buy &amp; Access Digital Social Logs', false);
    }
}

