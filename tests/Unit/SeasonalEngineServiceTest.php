<?php

namespace Tests\Unit;

use App\Models\SeasonalGift;
use App\Models\SeasonalGiftClaim;
use App\Models\SeasonalMode;
use App\Models\User;
use App\Services\SeasonalEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SeasonalEngineServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_all_ten_default_seasonal_modes_and_sample_gifts(): void
    {
        $engine = app(SeasonalEngineService::class);
        $engine->ensureDefaultsSeeded();

        $this->assertEquals(10, SeasonalMode::count());
        $this->assertTrue(SeasonalMode::where('slug', 'christmas-mode')->exists());
        $this->assertTrue(SeasonalMode::where('slug', 'new-year-mode')->exists());
        $this->assertTrue(SeasonalMode::where('slug', 'valentines-day-mode')->exists());
        $this->assertTrue(SeasonalMode::where('slug', 'easter-mode')->exists());
        $this->assertTrue(SeasonalMode::where('slug', 'halloween-mode')->exists());
        $this->assertTrue(SeasonalMode::where('slug', 'nigeria-independence-day-mode')->exists());
        $this->assertTrue(SeasonalMode::where('slug', 'black-friday-mode')->exists());
        $this->assertTrue(SeasonalMode::where('slug', 'may22sms-anniversary-mode')->exists());
        $this->assertTrue(SeasonalMode::where('slug', 'ramadan-eid-mode')->exists());
        $this->assertTrue(SeasonalMode::where('slug', 'custom-seasonal-mode')->exists());
        $this->assertGreaterThanOrEqual(10, SeasonalGift::count());
    }

    public function test_user_can_claim_seasonal_wallet_gift_and_duplicate_claim_is_prevented(): void
    {
        $engine = app(SeasonalEngineService::class);
        $engine->ensureDefaultsSeeded();

        $christmas = SeasonalMode::where('slug', 'christmas-mode')->firstOrFail();
        $christmas->update(['is_active' => true]);

        $gift = SeasonalGift::where('seasonal_mode_id', $christmas->id)->firstOrFail();
        $gift->update(['reward_value_usd' => 0.25, 'max_total_claims' => 10, 'max_claims_per_user' => 1]);

        $user = User::factory()->create([
            'walletBalance' => 1.00,
            'account_status' => 'active',
        ]);

        $result = $engine->claimGift($user, $gift, 'manual_claim', '127.0.0.1', 'PHPUnit');

        $this->assertEquals(0.25, $result['reward_usd']);
        $this->assertEquals(1.25, $result['new_balance_usd']);
        $this->assertEquals(1, SeasonalGiftClaim::where('user_id', $user->id)->where('seasonal_gift_id', $gift->id)->count());

        // Attempt duplicate claim -> must throw RuntimeException and leave balance unchanged
        $this->expectException(RuntimeException::class);
        $engine->claimGift($user, $gift, 'manual_claim', '127.0.0.1', 'PHPUnit');
    }

    public function test_scheduled_seasonal_mode_activates_and_expires_automatically(): void
    {
        $engine = app(SeasonalEngineService::class);
        $engine->ensureDefaultsSeeded();

        $newYear = SeasonalMode::where('slug', 'new-year-mode')->firstOrFail();
        $newYear->update([
            'is_active' => false,
            'is_scheduled' => true,
            'starts_at' => now()->subMinutes(10),
            'ends_at' => now()->addHours(2),
        ]);

        $res1 = $engine->syncScheduledModes();
        $this->assertContains('New Year Mode', $res1['activated']);
        $this->assertTrue($newYear->fresh()->is_active);

        // Move ends_at to the past and verify automatic deactivation
        $newYear->update(['ends_at' => now()->subMinute()]);
        $res2 = $engine->syncScheduledModes();
        $this->assertContains('New Year Mode', $res2['deactivated']);
        $this->assertFalse($newYear->fresh()->is_active);
    }

    public function test_batch_gift_distribution_to_selected_users(): void
    {
        $engine = app(SeasonalEngineService::class);
        $engine->ensureDefaultsSeeded();

        $gift = SeasonalGift::firstOrFail();
        $gift->update(['reward_value_usd' => 0.50]);

        $u1 = User::factory()->create(['walletBalance' => 0.00, 'account_status' => 'active']);
        $u2 = User::factory()->create(['walletBalance' => 0.00, 'account_status' => 'active']);

        $batch = $engine->distributeGiftBatch($gift, 'selected_users', [$u1->id, $u2->id]);

        $this->assertEquals(2, $batch['distributed_count']);
        $this->assertEquals(1.00, $batch['total_usd']);
        $this->assertEquals(0.50, (float) $u1->fresh()->walletBalance);
        $this->assertEquals(0.50, (float) $u2->fresh()->walletBalance);
    }

    public function test_advent_calendar_claiming_and_challenge_reward_and_badges(): void
    {
        $engine = app(SeasonalEngineService::class);
        $engine->ensureDefaultsSeeded();
        $engine->ensureInteractiveChristmasSeeded();

        $user = User::factory()->create([
            'walletBalance' => 1.00,
            'account_status' => 'active',
        ]);

        // 1. Claim unlocked Day 1 of Advent Calendar
        $adventRes = $engine->claimAdventDay($user, 1);
        $this->assertGreaterThan(0, $adventRes['reward_usd']);
        $this->assertEquals(1.00 + $adventRes['reward_usd'], $adventRes['new_balance_usd']);
        $this->assertNotEmpty($adventRes['earned_badges']);

        // 2. Complete tree decoration activity -> unlocks Christmas Tree Master badge & completes challenge
        $eval = $engine->recordActivityAndEvaluate($user, 'decorate_tree');
        $this->assertNotEmpty($eval['earned_badges']);
        $this->assertNotEmpty($eval['completed_challenges']);

        // 3. Claim completed challenge reward
        $treeChallenge = \App\Models\SeasonalChallenge::where('action_type', 'decorate_tree')->firstOrFail();
        $chalRes = $engine->claimChallengeReward($user->fresh(), $treeChallenge->id);
        $this->assertEquals((float) $treeChallenge->reward_value_usd, $chalRes['reward_usd']);
    }
}

