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
            'early_withdrawal_fee_percent' => 0.00,
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

        // User should receive principal (15,000 NGN = $10 USD) back into wallet
        $this->user->refresh();
        $this->assertEquals(100.00000000, (float)$this->user->walletBalance);

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
}
