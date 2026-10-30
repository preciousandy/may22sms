<?php

namespace Tests\Feature;

use App\Models\SavingsAuditLog;
use App\Models\SavingsCategory;
use App\Models\SavingsContribution;
use App\Models\SavingsInterestAccrual;
use App\Models\SavingsPlan;
use App\Models\Setting;
use App\Models\User;
use App\Services\SavingsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SavingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected User $admin;
    protected SavingsCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure exchange rate setting is present
        Setting::updateOrCreate(
            ['key' => 'ngn_per_usd'],
            ['value' => '1500']
        );

        // Create standard test category
        $this->category = SavingsCategory::create([
            'name' => 'Gadgets & Tech',
            'slug' => 'gadgets-and-tech',
            'description' => 'Target new phone, laptop or accessories',
            'icon' => '💻',
            'default_annual_rate' => 16.90,
            'min_deposit_ngn' => 1000.00,
            'min_deposit_usd' => 1.00,
            'min_duration_days' => 30,
            'max_duration_days' => 365,
            'allow_early_withdrawal' => true,
            'allow_partial_withdrawal' => false,
            'early_withdrawal_fee_percent' => 2.50,
            'early_withdrawal_interest_forfeited_percent' => 100.00,
            'maturity_fee_percent' => 1.50,
            'is_enabled' => true,
            'order_index' => 1,
        ]);

        // Create primary user with sufficient funds (e.g., $100 USD = 150,000 NGN)
        $this->user = User::factory()->create([
            'name' => 'Alice Cooper',
            'email' => 'alice@example.com',
            'walletBalance' => 100.00000000,
            'account_status' => 'active',
            'is_admin' => false,
        ]);

        // Create other user
        $this->otherUser = User::factory()->create([
            'name' => 'Bob Marley',
            'email' => 'bob@example.com',
            'walletBalance' => 50.00000000,
            'account_status' => 'active',
            'is_admin' => false,
        ]);

        // Create admin user
        $this->admin = User::factory()->create([
            'name' => 'Admin Boss',
            'email' => 'admin@example.com',
            'walletBalance' => 500.00000000,
            'account_status' => 'active',
            'is_admin' => true,
        ]);
    }

    public function test_user_can_view_savings_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get(route('savings.index'));
        $response->assertStatus(200);
        $response->assertSee('Save Small Small');
        $response->assertSee('Gadgets & Tech');
    }

    public function test_user_can_view_create_plan_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('savings.create', ['category' => $this->category->slug]));
        $response->assertStatus(200);
        $response->assertSee('Select Your Savings Goal');
        $response->assertSee('Back to Savings Dashboard');
        $response->assertSee('Plan Terms &amp; Financial Target', false);
    }

    public function test_user_can_create_savings_plan_and_wallet_is_debited_atomically(): void
    {
        // Initial deposit: 15,000 NGN = $10 USD at 1500 NGN/USD
        $initialDepositNgn = 15000;
        $targetNgn = 60000;
        $durationDays = 60;

        $response = $this->actingAs($this->user)->post(route('savings.store'), [
            'category_id' => $this->category->id,
            'name' => 'MacBook Fund',
            'description' => 'Saving up for M3 MacBook Pro',
            'currency' => 'NGN',
            'target_amount' => $targetNgn,
            'initial_deposit' => $initialDepositNgn,
            'duration_days' => $durationDays,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('savings_plans', [
            'user_id' => $this->user->id,
            'name' => 'MacBook Fund',
            'principal_amount' => 15000.00,
            'target_amount' => 60000.00,
            'status' => 'active',
        ]);

        // Check user wallet was debited by exactly $10 USD (15000 / 1500)
        $this->user->refresh();
        $this->assertEquals(90.00000000, (float)$this->user->walletBalance);

        // Check contribution was recorded
        $plan = SavingsPlan::where('name', 'MacBook Fund')->first();
        $this->assertNotNull($plan);
        $this->assertDatabaseHas('savings_contributions', [
            'savings_plan_id' => $plan->id,
            'amount' => 15000.00,
            'type' => 'deposit',
        ]);

        // Check audit log was created
        $this->assertDatabaseHas('savings_audit_logs', [
            'savings_plan_id' => $plan->id,
            'event' => 'plan_created',
        ]);
    }

    public function test_plan_creation_fails_if_insufficient_wallet_balance(): void
    {
        // User has $100 USD (150,000 NGN). Try to deposit 200,000 NGN ($133.33 USD)
        $response = $this->actingAs($this->user)->post(route('savings.store'), [
            'category_id' => $this->category->id,
            'name' => 'Huge Savings',
            'currency' => 'NGN',
            'target_amount' => 500000,
            'initial_deposit' => 200000,
            'duration_days' => 60,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('savings_plans', [
            'name' => 'Huge Savings',
        ]);

        // Balance unchanged
        $this->user->refresh();
        $this->assertEquals(100.00000000, (float)$this->user->walletBalance);
    }

    public function test_user_can_add_additional_contribution_to_active_plan(): void
    {
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Holiday Trip',
            'currency' => 'NGN',
            'target_amount' => 100000,
            'initial_deposit' => 15000, // Debits $10 USD
            'duration_days' => 45,
        ]);

        $this->user->refresh();
        $this->assertEquals(90.00000000, (float)$this->user->walletBalance);

        // Top up 30,000 NGN ($20 USD)
        $response = $this->actingAs($this->user)->post(route('savings.contribute', $plan->id), [
            'amount' => 30000,
        ]);

        $response->assertRedirect(route('savings.show', $plan->id));

        $plan->refresh();
        $this->assertEquals(45000.00, (float)$plan->principal_amount);

        // User debited additional $20 USD -> balance is 70.00
        $this->user->refresh();
        $this->assertEquals(70.00000000, (float)$this->user->walletBalance);

        // Contribution record created
        $this->assertDatabaseHas('savings_contributions', [
            'savings_plan_id' => $plan->id,
            'amount' => 30000.00,
            'type' => 'additional_deposit',
        ]);
    }

    public function test_idempotent_daily_interest_accrual(): void
    {
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Interest Test Plan',
            'currency' => 'NGN',
            'target_amount' => 50000,
            'initial_deposit' => 10000, // 10,000 NGN @ 16.9% P.A.
            'duration_days' => 60,
        ]);

        // Principal: 10,000 NGN
        // Expected daily interest = 10,000 * 0.169 / 365 = 4.63013699 NGN
        $targetDate = Carbon::tomorrow();

        // 1st run of interest accrual
        $accrual1 = $savingsService->accrueInterestForPlan($plan, $targetDate);
        $this->assertNotNull($accrual1);
        $this->assertEquals(4.63013699, round((float)$accrual1->day_interest_amount, 8));

        $plan->refresh();
        $this->assertEquals(4.63013699, round((float)$plan->accrued_interest, 8));

        // 2nd run on same target date (idempotency test)
        $accrual2 = $savingsService->accrueInterestForPlan($plan, $targetDate);
        $this->assertNull($accrual2, 'Duplicate accrual for the same date must be skipped');

        // Total accrued interest must remain unchanged
        $plan->refresh();
        $this->assertEquals(4.63013699, round((float)$plan->accrued_interest, 8));

        // Test running console command
        Artisan::call('savings:accrue-interest', ['--date' => $targetDate->toDateString()]);
        $plan->refresh();
        $this->assertEquals(4.63013699, round((float)$plan->accrued_interest, 8));
    }

    public function test_early_withdrawal_breaks_plan_and_forfeits_accrued_interest(): void
    {
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Emergency Fund',
            'currency' => 'NGN',
            'target_amount' => 30000,
            'initial_deposit' => 15000, // Debits $10 USD -> user balance $90
            'duration_days' => 60,
        ]);

        // Accrue 5 days of interest
        $simulatedDate = Carbon::tomorrow();
        for ($i = 0; $i < 5; $i++) {
            $savingsService->accrueInterestForPlan($plan, $simulatedDate->copy());
            $simulatedDate->addDay();
        }

        $plan->refresh();
        $this->assertGreaterThan(0, $plan->accrued_interest);
        $forfeitedInterest = (float)$plan->accrued_interest;

        // Perform early break
        $response = $this->actingAs($this->user)->post(route('savings.break', $plan->id), [
            'confirm_early_break' => '1',
        ]);

        $response->assertRedirect(route('savings.show', $plan->id));

        $plan->refresh();
        $this->assertEquals('broken', $plan->status);
        $this->assertEquals(0.00, (float)$plan->principal_amount);
        $this->assertEquals($forfeitedInterest, (float)$plan->forfeited_interest);

        // User should receive principal minus 2.5% fee: 15,000 NGN - 375 NGN fee = 14,625 NGN ($9.75 USD)
        // Wallet balance was $90.00 after creation -> $90.00 + $9.75 = $99.75 USD
        $this->user->refresh();
        $this->assertEquals(99.75000000, (float)$this->user->walletBalance);
        $this->assertEquals(375.00, (float)$plan->payout_statement['early_break_fee']);
        $this->assertEquals(14625.00, (float)$plan->payout_statement['net_payout']);
        $this->assertEquals(2.50, (float)$plan->payout_statement['early_break_fee_percent']);

        // Audit log recorded
        $this->assertDatabaseHas('savings_audit_logs', [
            'savings_plan_id' => $plan->id,
            'event' => 'early_withdrawal_executed',
        ]);
    }

    public function test_maturity_processing_credits_principal_and_interest_minus_maturity_fee(): void
    {
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Wedding Ring Fund',
            'currency' => 'NGN',
            'target_amount' => 50000,
            'initial_deposit' => 30000, // Debits $20 USD -> user balance $80
            'duration_days' => 30,
        ]);

        // Simulate 30 days of interest
        $dailyInterest = (30000 * 0.169) / 365; // ~13.8904 NGN / day
        $totalEarnedInterest = $dailyInterest * 30; // ~416.71 NGN
        $plan->accrued_interest = $totalEarnedInterest;
        $plan->start_date = Carbon::now()->subDays(31);
        $plan->maturity_date = Carbon::now()->subDay(); // Already matured
        $plan->save();

        // Process maturity
        $result = $savingsService->processMaturity($plan);
        $this->assertIsArray($result);

        $plan->refresh();
        $this->assertEquals('matured', $plan->status);
        $this->assertEquals(0.00, (float)$plan->principal_amount);

        // 1.5% fee on earned interest only
        $expectedFee = round($totalEarnedInterest * 0.015, 4);
        $netInterest = round($totalEarnedInterest - $expectedFee, 2);
        $this->assertEquals(round($netInterest, 2), round((float)$plan->paid_interest, 2));

        // User should have wallet credited with payout amount in USD
        $this->user->refresh();
        $this->assertGreaterThan(100.00, (float)$this->user->walletBalance);

        // Test scheduled maturity command picks up nothing new
        Artisan::call('savings:process-maturity');
    }

    public function test_user_cannot_view_or_break_another_users_plan(): void
    {
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Private Savings',
            'currency' => 'NGN',
            'target_amount' => 20000,
            'initial_deposit' => 5000,
            'duration_days' => 30,
        ]);

        // Bob tries to view Alice's plan
        $response = $this->actingAs($this->otherUser)->get(route('savings.show', $plan->id));
        $response->assertStatus(403);

        // Bob tries to break Alice's plan
        $breakResponse = $this->actingAs($this->otherUser)->post(route('savings.break', $plan->id), [
            'confirm_early_break' => '1',
        ]);
        $breakResponse->assertStatus(403);
    }

    public function test_admin_can_manage_categories_and_view_reconciliation(): void
    {
        // Non-admin gets redirected from admin savings
        $response = $this->actingAs($this->user)->get(route('admin.savings.index'));
        $response->assertRedirect();

        // Admin can view savings overview
        $adminOverview = $this->actingAs($this->admin)->get(route('admin.savings.index'));
        $adminOverview->assertStatus(200);
        $adminOverview->assertSee('Save Small Small');
        $adminOverview->assertSee('Reserves Reconciliation');

        // Admin can view categories management
        $categoriesPage = $this->actingAs($this->admin)->get(route('admin.savings.categories'));
        $categoriesPage->assertStatus(200);
        $categoriesPage->assertSee('Gadgets & Tech');

        // Admin can create a new category
        $createCatResponse = $this->actingAs($this->admin)->post(route('admin.savings.categories.store'), [
            'name' => 'Hajj & Umrah Savings',
            'description' => 'Dedicated pilgrimage savings plan',
            'icon' => '🕋',
            'default_annual_rate' => 17.50,
            'min_deposit_ngn' => 5000,
            'min_deposit_usd' => 5,
            'min_duration_days' => 60,
            'max_duration_days' => 730,
            'early_withdrawal_fee_percent' => 0.00,
            'early_withdrawal_interest_forfeited_percent' => 100.00,
            'maturity_fee_percent' => 1.50,
            'is_enabled' => '1',
        ]);

        $createCatResponse->assertRedirect();
        $this->assertDatabaseHas('savings_categories', [
            'name' => 'Hajj & Umrah Savings',
            'default_annual_rate' => 17.50,
        ]);

        // Admin can view reconciliation audit ledger
        $reconciliationPage = $this->actingAs($this->admin)->get(route('admin.savings.reconciliation'));
        $reconciliationPage->assertStatus(200);
        $reconciliationPage->assertSee('Reserves &amp; Liabilities Reconciliation', false);
    }

    public function test_admin_can_turn_off_interest_on_category_for_pure_target_savings(): void
    {
        // 1. Admin turns OFF 16.9% interest on Gadgets category
        $toggleResponse = $this->actingAs($this->admin)
            ->post(route('admin.savings.categories.toggle-interest', $this->category->id));
        $toggleResponse->assertRedirect();

        $this->category->refresh();
        $this->assertFalse((bool)$this->category->is_interest_enabled);
        $this->assertEquals(0.00, $this->category->effective_rate);

        // 2. User creates a savings plan in this zero-interest category
        $createResponse = $this->actingAs($this->user)->post(route('savings.store'), [
            'category_id' => $this->category->id,
            'name' => 'Zero-Interest Goal Vault',
            'currency' => 'NGN',
            'target_amount' => 50000,
            'initial_deposit' => 30000, // 30,000 NGN = $20 USD
            'duration_days' => 30,
        ]);

        $createResponse->assertRedirect();
        $plan = SavingsPlan::where('name', 'Zero-Interest Goal Vault')->first();
        $this->assertNotNull($plan);
        $this->assertEquals(0.00, (float)$plan->annual_interest_rate);
        $this->assertEquals(30000.00, (float)$plan->principal_amount);

        // 3. Daily interest accrual command skips plan with 0% interest
        $savingsService = app(SavingsService::class);
        $accrual = $savingsService->accrueInterestForPlan($plan, Carbon::tomorrow());
        $this->assertNull($accrual);
        $plan->refresh();
        $this->assertEquals(0.00, (float)$plan->accrued_interest);

        // 4. At maturity: User receives back exactly what they saved (100% of 30,000 NGN = $20 USD)
        // with 0% fee and 0 interest
        $plan->start_date = Carbon::now()->subDays(31);
        $plan->maturity_date = Carbon::now()->subDay();
        $plan->save();

        $balanceBefore = (float)$this->user->refresh()->walletBalance; // $80.00
        $result = $savingsService->processMaturity($plan);
        $this->assertIsArray($result);

        $plan->refresh();
        $this->assertEquals('matured', $plan->status);
        $this->assertEquals(0.00, (float)$plan->principal_amount);
        $this->assertEquals(0.00, (float)$plan->paid_interest);

        // Statement confirms user gets back exact deposit with 0 fee
        $this->assertEquals(30000.00, (float)$plan->payout_statement['principal']);
        $this->assertEquals(0.00, (float)$plan->payout_statement['maturity_fee']);
        $this->assertEquals(30000.00, (float)$plan->payout_statement['total_payout']);

        // User wallet credited with exact $20 USD
        $this->user->refresh();
        $this->assertEquals(round($balanceBefore + 20.00, 2), round((float)$this->user->walletBalance, 2));

        // 5. Admin turns interest back ON for this category
        $turnOnResponse = $this->actingAs($this->admin)
            ->post(route('admin.savings.categories.toggle-interest', $this->category->id));
        $turnOnResponse->assertRedirect();

        $this->category->refresh();
        $this->assertTrue((bool)$this->category->is_interest_enabled);
        $this->assertEquals(16.90, $this->category->effective_rate);
    }

    public function test_admin_can_turn_off_interest_globally(): void
    {
        // Admin turns OFF global interest
        $response = $this->actingAs($this->admin)->post(route('admin.savings.toggle-global-interest'), [
            'global_interest_enabled' => '0',
        ]);
        $response->assertRedirect();

        $this->assertEquals('0', Setting::where('key', 'savings_global_interest_enabled')->value('value'));
        $this->assertEquals(0.00, $this->category->refresh()->effective_rate);

        // New plan created while global interest is off has 0.00% rate
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Global Off Plan',
            'currency' => 'NGN',
            'target_amount' => 10000,
            'initial_deposit' => 3000,
            'duration_days' => 30,
        ]);

        $this->assertEquals(0.00, (float)$plan->annual_interest_rate);

        // Turn global interest back on
        $turnOn = $this->actingAs($this->admin)->post(route('admin.savings.toggle-global-interest'), [
            'global_interest_enabled' => '1',
        ]);
        $turnOn->assertRedirect();
        $this->assertEquals('1', Setting::where('key', 'savings_global_interest_enabled')->value('value'));
        $this->assertEquals(16.90, $this->category->refresh()->effective_rate);
    }

    /**
     * 1. Early withdrawal with accrued interest:
     * Example from spec:
     * Savings principal: ₦100,000
     * Accrued interest: ₦5,000
     * Early-break fee (2.5%): ₦2,500
     * Accrued interest forfeited: ₦5,000
     * Amount returned to the main wallet: ₦97,500
     */
    public function test_early_withdrawal_with_accrued_interest(): void
    {
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Car Purchase Fund',
            'currency' => 'NGN',
            'target_amount' => 200000,
            'initial_deposit' => 100000, // Debits $66.66666667 USD (at 1500 NGN/USD)
            'duration_days' => 90,
        ]);

        // Simulate ₦5,000 accrued interest
        $plan->accrued_interest = 5000.00;
        $plan->save();

        $balanceBeforeBreak = (float)$this->user->refresh()->walletBalance;

        // User breaks plan early
        $response = $this->actingAs($this->user)->post(route('savings.break', $plan->id), [
            'confirm_early_break' => '1',
        ]);
        $response->assertRedirect(route('savings.show', $plan->id));

        $plan->refresh();
        $this->assertEquals('broken', $plan->status);
        $this->assertEquals(0.00, (float)$plan->principal_amount);
        $this->assertEquals(5000.00, (float)$plan->forfeited_interest);
        $this->assertEquals(0.00, (float)$plan->paid_interest);

        // Fee: ₦2,500 (2.5% of ₦100,000). Net payout: ₦97,500
        $this->assertEquals(2500.00, (float)$plan->payout_statement['early_break_fee']);
        $this->assertEquals(97500.00, (float)$plan->payout_statement['net_payout']);
        $this->assertEquals(5000.00, (float)$plan->payout_statement['forfeited_interest']);

        // Main wallet credited exactly ₦97,500 ($65.00 USD at 1500 NGN/USD)
        $expectedWallet = round($balanceBeforeBreak + (97500 / 1500), 8);
        $this->assertEquals($expectedWallet, round((float)$this->user->refresh()->walletBalance, 8));

        // User NEVER receives the ₦5,000 forfeited interest
        $this->assertNotEquals(round($balanceBeforeBreak + (102500 / 1500), 8), round((float)$this->user->walletBalance, 8));
    }

    /**
     * 2. Early withdrawal when no interest has accrued.
     */
    public function test_early_withdrawal_when_no_interest_has_accrued(): void
    {
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Zero Interest Break Plan',
            'currency' => 'NGN',
            'target_amount' => 150000,
            'initial_deposit' => 100000,
            'duration_days' => 60,
        ]);

        $this->assertEquals(0.00, (float)$plan->accrued_interest);
        $balanceBeforeBreak = (float)$this->user->refresh()->walletBalance;

        $response = $this->actingAs($this->user)->post(route('savings.break', $plan->id), [
            'confirm_early_break' => '1',
        ]);
        $response->assertRedirect(route('savings.show', $plan->id));

        $plan->refresh();
        $this->assertEquals('broken', $plan->status);
        $this->assertEquals(0.00, (float)$plan->forfeited_interest);
        $this->assertEquals(2500.00, (float)$plan->payout_statement['early_break_fee']);
        $this->assertEquals(97500.00, (float)$plan->payout_statement['net_payout']);

        // Exactly ₦97,500 ($65 USD) returned
        $expectedWallet = round($balanceBeforeBreak + (97500 / 1500), 8);
        $this->assertEquals($expectedWallet, round((float)$this->user->refresh()->walletBalance, 8));
    }

    /**
     * 3. Correct calculation of the 2.5% fee on various amounts.
     */
    public function test_correct_calculation_of_2_point_5_percent_fee(): void
    {
        $savingsService = app(SavingsService::class);

        // Test ₦100,000
        $plan1 = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Calc Test 1',
            'currency' => 'NGN',
            'initial_deposit' => 100000,
            'duration_days' => 30,
        ]);
        $summary1 = $plan1->calculateEarlyBreakSummary();
        $this->assertEquals(2500.00, $summary1['early_break_fee']);
        $this->assertEquals(97500.00, $summary1['net_payout']);
        $this->assertEquals(2.50, $summary1['early_break_fee_percent']);

        // Test ₦50,000
        $plan2 = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Calc Test 2',
            'currency' => 'NGN',
            'initial_deposit' => 50000,
            'duration_days' => 30,
        ]);
        $summary2 = $plan2->calculateEarlyBreakSummary();
        $this->assertEquals(1250.00, $summary2['early_break_fee']);
        $this->assertEquals(48750.00, $summary2['net_payout']);

        // Test USD currency ($100 USD)
        $this->user->walletBalance = 200.00;
        $this->user->save();
        $planUsd = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'USD Plan',
            'currency' => 'USD',
            'initial_deposit' => 100,
            'duration_days' => 30,
        ]);
        $summaryUsd = $planUsd->calculateEarlyBreakSummary();
        $this->assertEquals(2.50, $summaryUsd['early_break_fee']);
        $this->assertEquals(97.50, $summaryUsd['net_payout']);
    }

    /**
     * 4. Correct net payout to the main wallet.
     */
    public function test_correct_net_payout_to_main_wallet(): void
    {
        $savingsService = app(SavingsService::class);

        $initialWalletBalance = (float)$this->user->walletBalance; // 100 USD
        $depositNgn = 60000; // $40 USD
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Net Payout Test',
            'currency' => 'NGN',
            'initial_deposit' => $depositNgn,
            'duration_days' => 45,
        ]);

        $this->assertEquals(round($initialWalletBalance - 40.00, 2), round((float)$this->user->refresh()->walletBalance, 2));

        // Break early: Fee = 60,000 * 2.5% = 1,500 NGN ($1.00 USD)
        // Net payout = 58,500 NGN ($39.00 USD)
        $savingsService->breakPlan($plan, $this->user);

        // Wallet was 60 + 39 = 99 USD
        $this->assertEquals(round($initialWalletBalance - 1.00, 2), round((float)$this->user->refresh()->walletBalance, 2));
    }

    /**
     * 5. Complete forfeiture of accrued interest.
     */
    public function test_complete_forfeiture_of_accrued_interest(): void
    {
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Forfeiture Test',
            'currency' => 'NGN',
            'initial_deposit' => 30000,
            'duration_days' => 60,
        ]);

        $plan->accrued_interest = 1250.75;
        $plan->save();

        $walletBefore = (float)$this->user->refresh()->walletBalance;

        $savingsService->breakPlan($plan, $this->user);
        $plan->refresh();

        $this->assertEquals(1250.75, (float)$plan->forfeited_interest);
        $this->assertEquals(0.00, (float)$plan->paid_interest);

        // Payout to wallet is strictly 30,000 - (30,000 * 0.025) = 29,250 NGN ($19.50 USD)
        $walletAfter = (float)$this->user->refresh()->walletBalance;
        $this->assertEquals(round($walletBefore + 19.50, 4), round($walletAfter, 4));
    }

    /**
     * 6. Withdrawal after maturity without the early-break penalty.
     */
    public function test_withdrawal_after_maturity_without_the_early_break_penalty(): void
    {
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Matured Plan Test',
            'currency' => 'NGN',
            'initial_deposit' => 100000, // $66.66666667 USD
            'duration_days' => 30,
        ]);

        // Fast-forward maturity to yesterday and add interest
        $plan->start_date = Carbon::today()->subDays(35);
        $plan->maturity_date = Carbon::today()->subDays(5);
        $plan->accrued_interest = 4000.00;
        $plan->save();

        $this->assertTrue($plan->isMatured());

        $walletBefore = (float)$this->user->refresh()->walletBalance;

        // User initiates withdrawal via breakPlan (or normal withdraw)
        $response = $this->actingAs($this->user)->post(route('savings.break', $plan->id), [
            'confirm_early_break' => '1',
        ]);
        $response->assertRedirect(route('savings.show', $plan->id));

        $plan->refresh();

        // Must be marked as matured, NOT broken!
        $this->assertEquals('matured', $plan->status);
        $this->assertNull($plan->broken_at);
        $this->assertNotNull($plan->matured_at);

        // Statement confirms maturity payout: NO early break fee!
        $this->assertEquals('maturity_payout', $plan->payout_statement['type']);
        $this->assertArrayNotHasKey('early_break_fee', $plan->payout_statement);
        $this->assertEquals(100000.00, (float)$plan->payout_statement['principal']);

        // Maturity fee is 1.5% of EARNED INTEREST (4,000 * 0.015 = 60 NGN)
        // Net interest = 3,940 NGN. Total payout = 103,940 NGN
        $this->assertEquals(60.00, (float)$plan->payout_statement['maturity_fee']);
        $this->assertEquals(3940.00, (float)$plan->payout_statement['net_interest']);
        $this->assertEquals(103940.00, (float)$plan->payout_statement['total_payout']);

        // Wallet was credited full principal + net interest without 2.5% penalty
        $expectedWallet = round($walletBefore + (103940 / 1500), 4);
        $this->assertEquals($expectedWallet, round((float)$this->user->refresh()->walletBalance, 4));
    }

    /**
     * 7. Insufficient or inconsistent balances cannot be broken.
     */
    public function test_insufficient_or_inconsistent_balances(): void
    {
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Empty Plan Test',
            'currency' => 'NGN',
            'initial_deposit' => 15000,
            'duration_days' => 30,
        ]);

        // Manually corrupt plan principal to 0
        $plan->principal_amount = 0.00;
        $plan->save();

        $walletBefore = (float)$this->user->refresh()->walletBalance;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No eligible savings principal available to withdraw.');

        $savingsService->breakPlan($plan, $this->user);

        // Wallet remains untouched
        $this->assertEquals($walletBefore, (float)$this->user->refresh()->walletBalance);
    }

    /**
     * 8. Duplicate withdrawal requests are strictly rejected.
     */
    public function test_duplicate_withdrawal_requests_prevented(): void
    {
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Duplicate Prevention Plan',
            'currency' => 'NGN',
            'initial_deposit' => 30000,
            'duration_days' => 30,
        ]);

        // 1st withdrawal succeeds
        $result = $savingsService->breakPlan($plan, $this->user);
        $this->assertTrue($result['success']);

        $walletAfterFirst = (float)$this->user->refresh()->walletBalance;

        // 2nd withdrawal attempt throws exception
        try {
            $savingsService->breakPlan($plan, $this->user);
            $this->fail('Second withdrawal attempt must throw InvalidArgumentException');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('closed or processed', $e->getMessage());
        }

        // Wallet balance must NOT be credited a second time
        $this->assertEquals($walletAfterFirst, (float)$this->user->refresh()->walletBalance);
    }

    /**
     * 9. Concurrent withdrawal attempts: second transaction detects inactive plan.
     */
    public function test_concurrent_withdrawal_attempts_prevented(): void
    {
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Concurrent Plan',
            'currency' => 'NGN',
            'initial_deposit' => 30000,
            'duration_days' => 30,
        ]);

        // Simulate concurrent execution: first transaction closes the plan
        $savingsService->breakPlan($plan, $this->user);

        // Concurrent attempt fails validation under lock
        $this->expectException(\InvalidArgumentException::class);
        $savingsService->breakPlan($plan, $this->user);
    }

    /**
     * 10. Database failures and rollback behavior.
     */
    public function test_database_failures_and_rollback_behavior(): void
    {
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Rollback Plan',
            'currency' => 'NGN',
            'initial_deposit' => 30000,
            'duration_days' => 30,
        ]);

        $walletBefore = (float)$this->user->refresh()->walletBalance;

        // Mock WalletService to simulate a failure during credit
        $mockWallet = \Mockery::mock(\App\Services\WalletService::class);
        $mockWallet->shouldReceive('convertToUsd')->andReturn(20.0);
        $mockWallet->shouldReceive('credit')->andThrow(new \RuntimeException('Simulated payment gateway / DB deadlock failure'));

        $serviceWithMock = new SavingsService($mockWallet);

        try {
            $serviceWithMock->breakPlan($plan, $this->user);
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Simulated payment gateway', $e->getMessage());
        }

        // Verify full database rollback: Plan remains ACTIVE and principal intact
        $plan->refresh();
        $this->assertEquals('active', $plan->status);
        $this->assertEquals(30000.00, (float)$plan->principal_amount);
        $this->assertNull($plan->broken_at);

        // User wallet balance is unchanged
        $this->assertEquals($walletBefore, (float)$this->user->refresh()->walletBalance);
    }

    /**
     * 11. Prevention of further interest accrual after the break.
     */
    public function test_prevention_of_further_interest_accrual_after_the_break(): void
    {
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Stop Accrual Plan',
            'currency' => 'NGN',
            'initial_deposit' => 20000,
            'duration_days' => 60,
        ]);

        // Break plan
        $savingsService->breakPlan($plan, $this->user);
        $plan->refresh();
        $this->assertEquals('broken', $plan->status);
        $this->assertEquals(0.00, (float)$plan->principal_amount);

        // Attempt interest accrual on broken plan
        $accrual = $savingsService->accrueInterestForPlan($plan, Carbon::tomorrow());
        $this->assertNull($accrual, 'Broken plans must not accrue any interest.');

        // Run console command
        Artisan::call('savings:accrue-interest', ['--date' => Carbon::tomorrow()->toDateString()]);
        $plan->refresh();
        $this->assertEquals(0.00, (float)$plan->accrued_interest);
    }

    /**
     * 12. Confirmation cancellation without financial changes.
     */
    public function test_confirmation_cancellation_without_financial_changes(): void
    {
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Cancel Test Plan',
            'currency' => 'NGN',
            'initial_deposit' => 25000,
            'duration_days' => 45,
        ]);

        $walletBefore = (float)$this->user->refresh()->walletBalance;

        // Post without confirm_early_break checkbox (cancelled by user)
        $response = $this->actingAs($this->user)->post(route('savings.break', $plan->id), [
            'confirm_early_break' => '0',
        ]);
        $response->assertSessionHasErrors('confirm_early_break');

        $plan->refresh();
        $this->assertEquals('active', $plan->status);
        $this->assertEquals(25000.00, (float)$plan->principal_amount);
        $this->assertEquals($walletBefore, (float)$this->user->refresh()->walletBalance);
    }

    /**
     * Admin can adjust the early break fee for each saving option.
     */
    public function test_admin_can_adjust_breaking_fee_for_each_saving_option(): void
    {
        // 1. Admin adjusts breaking fee for Gadgets & Tech from 2.5% to 4.5%
        $response = $this->actingAs($this->admin)->post(
            route('admin.savings.categories.update-break-fee', $this->category->id),
            ['early_withdrawal_fee_percent' => 4.50]
        );
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->category->refresh();
        $this->assertEquals(4.50, (float)$this->category->early_withdrawal_fee_percent);

        // 2. User creates a plan under this category with ₦100,000 principal
        $savingsService = app(SavingsService::class);
        $plan = $savingsService->createPlan($this->user, [
            'category_id' => $this->category->id,
            'name' => 'Tech Gadgets SSS',
            'currency' => 'NGN',
            'initial_deposit' => 100000,
            'duration_days' => 60,
        ]);

        $summary = $plan->calculateEarlyBreakSummary();
        // Fee charged is 4.5% of ₦100,000 = ₦4,500. Net payout = ₦95,500
        $this->assertEquals(4.50, $summary['early_break_fee_percent']);
        $this->assertEquals(4500.00, $summary['early_break_fee']);
        $this->assertEquals(95500.00, $summary['net_payout']);

        // 3. User breaks plan early: verified against adjusted fee
        $savingsService->breakPlan($plan, $this->user);
        $plan->refresh();

        $this->assertEquals(4500.00, (float)$plan->payout_statement['early_break_fee']);
        $this->assertEquals(95500.00, (float)$plan->payout_statement['net_payout']);
        $this->assertEquals(4.50, (float)$plan->payout_statement['early_break_fee_percent']);

        // Verify audit log exists
        $this->assertDatabaseHas('savings_audit_logs', [
            'event' => 'category_break_fee_updated',
        ]);
    }
}
