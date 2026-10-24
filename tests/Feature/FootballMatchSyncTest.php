<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FootballMatchSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_admin' => true,
            'account_status' => 'active',
            'email' => 'admin@may22sms.com',
        ]);

        $this->user = User::factory()->create([
            'is_admin' => false,
            'account_status' => 'active',
            'email' => 'user@may22sms.com',
        ]);
    }

    public function test_admin_can_view_football_matches_page_with_sync_settings_button(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/football-matches');

        $response->assertStatus(200);
        $response->assertSee('Football Matches');
        $response->assertSee('Sync Settings');
        $response->assertSee('Sync Now');
        $response->assertSee('Football Sync Settings');
    }

    public function test_admin_can_save_football_sync_settings(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/football-matches/settings', [
            'football_data_api_token' => 'test_api_token_12345',
            'football_sync_days' => 14,
            'football_auto_sync_enabled' => '1',
            'action' => 'save',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('test_api_token_12345', Setting::where('key', 'football_data_api_token')->value('value'));
        $this->assertEquals('14', Setting::where('key', 'football_sync_days')->value('value'));
        $this->assertEquals('1', Setting::where('key', 'football_auto_sync_enabled')->value('value'));
    }

    public function test_user_page_displays_sync_settings_button_for_admin(): void
    {
        // For admin: sees Sync Settings button
        $adminView = $this->actingAs($this->admin)->get('/football-matches');
        $adminView->assertStatus(200);
        $adminView->assertSee('Sync Settings');

        // For regular user: does not see admin button
        $userView = $this->actingAs($this->user)->get('/football-matches');
        $userView->assertStatus(200);
        $userView->assertDontSee('/admin/football-matches');
    }

    public function test_console_command_skips_when_auto_sync_disabled(): void
    {
        Setting::updateOrCreate(
            ['key' => 'football_auto_sync_enabled'],
            ['value' => '0']
        );

        $this->artisan('app:sync-football-matches')
            ->expectsOutput('Football auto-sync is disabled in settings. Skipping.')
            ->assertExitCode(0);
    }
}
