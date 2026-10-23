<?php

namespace Tests\Unit;

use App\Models\DailyLoginGift;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DailyLoginGiftTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_daily_gift_status()
    {
        $user = User::factory()->create([
            'account_status' => 'active',
            'walletBalance' => 0.00,
        ]);

        Setting::updateOrCreate(['key' => 'daily_gift_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'daily_gift_max_claims_per_day'], ['value' => '25']);

        $response = $this->actingAs($user)->getJson('/api/daily-gift/status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'enabled' => true,
                'has_claimed_today' => false,
                'streak' => 1,
                'max_claims_per_day' => 25,
                'pool_exhausted' => false,
            ]);
    }

    public function test_user_can_claim_daily_gift_and_wallet_is_credited()
    {
        $user = User::factory()->create([
            'account_status' => 'active',
            'walletBalance' => 1.00,
        ]);

        Setting::updateOrCreate(['key' => 'daily_gift_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'daily_gift_type'], ['value' => 'streak']);
        Setting::updateOrCreate(['key' => 'daily_gift_streak_amounts'], ['value' => json_encode([
            1 => 0.05,
            2 => 0.10,
            3 => 0.15,
            4 => 0.20,
            5 => 0.25,
            6 => 0.30,
            7 => 0.50,
        ])]);

        $response = $this->actingAs($user)->postJson('/api/daily-gift/claim');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'streak' => 1,
                'amount' => 0.05,
                'new_balance' => 1.05,
            ]);

        $this->assertDatabaseHas('daily_login_gifts', [
            'user_id' => $user->id,
            'amount_usd' => 0.05,
            'streak' => 1,
        ]);

        $giftRecord = DailyLoginGift::where('user_id', $user->id)->first();
        $this->assertNotNull($giftRecord);
        $this->assertEquals(Carbon::today()->toDateString(), Carbon::parse($giftRecord->claimed_date)->toDateString());

        $freshUser = User::find($user->id);
        $this->assertEquals(1.05, (float) $freshUser->walletBalance);
    }

    public function test_user_cannot_claim_twice_on_the_same_day()
    {
        $user = User::factory()->create([
            'account_status' => 'active',
            'walletBalance' => 0.00,
        ]);

        Setting::updateOrCreate(['key' => 'daily_gift_enabled'], ['value' => '1']);

        $first = $this->actingAs($user)->postJson('/api/daily-gift/claim');
        $first->assertStatus(200);

        $second = $this->actingAs($user)->postJson('/api/daily-gift/claim');
        $second->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertEquals(1, DailyLoginGift::where('user_id', $user->id)->count());
    }

    public function test_daily_user_quota_limit_blocks_claims_once_reached()
    {
        $user1 = User::factory()->create(['account_status' => 'active', 'walletBalance' => 0.00]);
        $user2 = User::factory()->create(['account_status' => 'active', 'walletBalance' => 0.00]);

        // Max 1 user can claim today
        Setting::updateOrCreate(['key' => 'daily_gift_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'daily_gift_max_claims_per_day'], ['value' => '1']);

        // User 1 claims: succeeds
        $res1 = $this->actingAs($user1)->postJson('/api/daily-gift/claim');
        $res1->assertStatus(200);

        // User 2 claims: blocked by daily quota
        $res2 = $this->actingAs($user2)->postJson('/api/daily-gift/claim');
        $res2->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertStringContainsString('limit', strtolower($res2->json('message')));
        $this->assertEquals(1, DailyLoginGift::getTodayClaimsCount());
    }

    public function test_streak_increments_on_consecutive_days_and_resets_if_skipped()
    {
        $user = User::factory()->create(['account_status' => 'active', 'walletBalance' => 0.00]);

        // Case A: No previous claim -> next streak is 1
        $this->assertEquals(1, DailyLoginGift::calculateNextStreak($user->id));

        // Case B: Yesterday claim (Day 3) -> next streak is 4
        DailyLoginGift::create([
            'user_id' => $user->id,
            'amount_usd' => 0.06,
            'streak' => 3,
            'claimed_date' => Carbon::yesterday()->toDateString(),
        ]);
        $this->assertEquals(4, DailyLoginGift::calculateNextStreak($user->id));

        // Case C: Day 7 yesterday -> wraps around to 1
        DailyLoginGift::where('user_id', $user->id)->delete();
        DailyLoginGift::create([
            'user_id' => $user->id,
            'amount_usd' => 0.25,
            'streak' => 7,
            'claimed_date' => Carbon::yesterday()->toDateString(),
        ]);
        $this->assertEquals(1, DailyLoginGift::calculateNextStreak($user->id));

        // Case D: Skipped day (claimed 2 days ago) -> resets to 1
        DailyLoginGift::where('user_id', $user->id)->delete();
        DailyLoginGift::create([
            'user_id' => $user->id,
            'amount_usd' => 0.06,
            'streak' => 5,
            'claimed_date' => Carbon::today()->subDays(2)->toDateString(),
        ]);
        $this->assertEquals(1, DailyLoginGift::calculateNextStreak($user->id));
    }

    public function test_disabled_daily_gift_blocks_claim()
    {
        $user = User::factory()->create(['account_status' => 'active', 'walletBalance' => 0.00]);

        Setting::updateOrCreate(['key' => 'daily_gift_enabled'], ['value' => '0']);

        $response = $this->actingAs($user)->postJson('/api/daily-gift/claim');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_admin_settings_can_update_daily_gift_configuration()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'account_status' => 'active',
        ]);

        $streakData = [
            1 => 0.03,
            2 => 0.06,
            3 => 0.09,
            4 => 0.12,
            5 => 0.15,
            6 => 0.20,
            7 => 0.35,
        ];

        $response = $this->actingAs($admin)->post('/admin/settings', [
            'daily_gift_enabled' => '1',
            'daily_gift_max_claims_per_day' => '100',
            'daily_gift_type' => 'streak',
            'daily_gift_streak_amounts' => $streakData,
        ]);

        $response->assertRedirect();

        $this->assertEquals('1', Setting::where('key', 'daily_gift_enabled')->value('value'));
        $this->assertEquals('100', Setting::where('key', 'daily_gift_max_claims_per_day')->value('value'));
        $this->assertEquals('streak', Setting::where('key', 'daily_gift_type')->value('value'));

        $savedStreak = json_decode(Setting::where('key', 'daily_gift_streak_amounts')->value('value'), true);
        $this->assertEquals(0.03, (float) $savedStreak[1]);
        $this->assertEquals(0.35, (float) $savedStreak[7]);
    }
}
