<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_wallet()
    {
        $response = $this->get('/wallet');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_wallet_ledger()
    {
        $user = User::factory()->create([
            'account_status' => 'active',
            'walletBalance' => 25.50,
        ]);

        WalletTransaction::create([
            'user_id' => $user->id,
            'amount_usd' => 10.00,
            'type' => 'funding',
            'reference' => 'FUND-TEST-100',
            'balance_before' => 15.50,
            'balance_after' => 25.50,
        ]);

        WalletTransaction::create([
            'user_id' => $user->id,
            'amount_usd' => 0.05,
            'type' => 'daily_gift',
            'reference' => 'GIFT-TEST-100',
            'balance_before' => 25.50,
            'balance_after' => 25.55,
        ]);

        $response = $this->actingAs($user)->get('/wallet');

        $response->assertStatus(200)
            ->assertSee('Wallet Ledger')
            ->assertSee('Transaction History')
            ->assertSee('FUND-TEST-100')
            ->assertSee('GIFT-TEST-100');
    }
}
