<?php

namespace Tests\Unit;

use App\Models\SystemTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_impersonate_user_and_stop_impersonating()
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'account_status' => 'active',
            'is_admin' => true,
        ]);

        $targetUser = User::factory()->create([
            'name' => 'John Customer',
            'email' => 'john@example.com',
            'email_verified_at' => now(),
            'account_status' => 'active',
            'is_admin' => false,
        ]);

        // Log in as Admin
        $this->actingAs($admin);

        // Request 1-click impersonation
        $response = $this->post("/admin/users/{$targetUser->id}/impersonate");

        $response->assertRedirect('/dashboard');
        $this->assertEquals($targetUser->id, auth()->id());
        $this->assertEquals($admin->id, session('impersonator_id'));

        // Check system audit log recorded
        $log = SystemTransaction::where('user_id', $targetUser->id)
            ->where('action', 'impersonate_login')
            ->first();
        $this->assertNotNull($log);
        $this->assertEquals($admin->id, $log->actor_user_id);

        // Stop impersonating and return to admin session
        $stopResponse = $this->post('/admin/users/stop-impersonating');
        $stopResponse->assertRedirect('/admin/users');
        $this->assertEquals($admin->id, auth()->id());
        $this->assertFalse(session()->has('impersonator_id'));
    }

    public function test_admin_can_view_user_details_page()
    {
        $admin = User::factory()->create([
            'account_status' => 'active',
            'is_admin' => true,
        ]);

        $targetUser = User::factory()->create([
            'account_status' => 'active',
            'is_admin' => false,
        ]);

        $response = $this->actingAs($admin)->get("/admin/users/{$targetUser->id}");

        $response->assertStatus(200);
        $response->assertSee($targetUser->name);
        $response->assertSee('Adjust Wallet Balance');
    }
}
