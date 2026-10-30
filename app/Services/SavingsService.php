<?php

namespace App\Services;

use App\Models\SavingsAuditLog;
use App\Models\SavingsCategory;
use App\Models\SavingsContribution;
use App\Models\SavingsInterestAccrual;
use App\Models\SavingsPlan;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class SavingsService
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get the live NGN to USD exchange rate from settings.
     */
    public function getNgnPerUsd(): float
    {
        return (float) (Setting::where('key', 'ngn_per_usd')->value('value') ?? 1500.0);
    }

    /**
     * Convert local currency amount (NGN or USD) into wallet base currency (USD).
     */
    public function convertToUsd(float $amount, string $currency): float
    {
        if (strtoupper($currency) === 'USD') {
            return round($amount, 8);
        }

        $rate = $this->getNgnPerUsd();
        if ($rate <= 0) {
            $rate = 1500.0;
        }

        return round($amount / $rate, 8);
    }

    /**
     * Convert wallet base currency (USD) into local currency amount.
     */
    public function convertFromUsd(float $amountUsd, string $currency): float
    {
        if (strtoupper($currency) === 'USD') {
            return round($amountUsd, 2);
        }

        $rate = $this->getNgnPerUsd();
        return round($amountUsd * $rate, 2);
    }

    /**
     * Create a new Save Small Small plan and fund it from main wallet.
     */
    public function createPlan(User $user, array $data): SavingsPlan
    {
        $category = SavingsCategory::findOrFail($data['category_id']);
        if (! $category->is_enabled) {
            throw new InvalidArgumentException('This savings category is currently disabled.');
        }

        $currency = strtoupper($data['currency'] ?? 'NGN');
        $initialDeposit = (float) ($data['initial_deposit'] ?? 0);
        $targetAmount = !empty($data['target_amount']) ? (float) $data['target_amount'] : null;
        $durationDays = (int) ($data['duration_days'] ?? $category->min_duration_days);

        // Validation against category rules
        $minDeposit = $currency === 'USD' ? $category->min_deposit_usd : $category->min_deposit_ngn;
        if ($initialDeposit < $minDeposit) {
            $currSymbol = $currency === 'USD' ? '$' : '₦';
            throw new InvalidArgumentException("Minimum initial deposit for {$category->name} is {$currSymbol}" . number_format($minDeposit, 2));
        }

        if ($durationDays < $category->min_duration_days || $durationDays > $category->max_duration_days) {
            throw new InvalidArgumentException("Duration must be between {$category->min_duration_days} and {$category->max_duration_days} days.");
        }

        $amountUsd = $this->convertToUsd($initialDeposit, $currency);

        return DB::transaction(function () use ($user, $category, $data, $currency, $initialDeposit, $targetAmount, $durationDays, $amountUsd) {
            // Pessimistic lock on user to prevent race conditions & negative balance
            $lockedUser = User::lockForUpdate()->findOrFail($user->id);
            if ((float) $lockedUser->walletBalance < $amountUsd) {
                throw new RuntimeException('Insufficient funds in your main wallet to create this savings plan.');
            }

            $planReference = 'SSS-PLAN-' . strtoupper(Str::random(10));
            $txReference = 'SSS-DEP-' . strtoupper(Str::random(12));

            // Debit main wallet atomically
            $this->walletService->debit(
                $lockedUser,
                $amountUsd,
                'savings_deposit',
                $txReference,
                [
                    'plan_reference' => $planReference,
                    'category' => $category->name,
                    'currency' => $currency,
                    'amount_local' => $initialDeposit,
                ]
            );

            $startDate = Carbon::today();
            $commencementDate = Carbon::tomorrow(); // Additional/initial contributions earn interest next eligible day
            $maturityDate = Carbon::today()->addDays($durationDays);

            // Create immutable savings plan contract
            $plan = SavingsPlan::create([
                'user_id' => $lockedUser->id,
                'category_id' => $category->id,
                'reference' => $planReference,
                'name' => trim($data['name'] ?? $category->name),
                'description' => trim($data['description'] ?? ''),
                'currency' => $currency,
                'target_amount' => $targetAmount,
                'principal_amount' => $initialDeposit,
                'accrued_interest' => 0.00000000,
                'paid_interest' => 0.00000000,
                'forfeited_interest' => 0.00000000,
                'total_withdrawn' => 0.00000000,
                'annual_interest_rate' => $category->effective_rate,
                'interest_commencement_date' => $commencementDate,
                'last_accrual_date' => null,
                'start_date' => $startDate,
                'maturity_date' => $maturityDate,
                'duration_days' => $durationDays,
                'allow_early_withdrawal' => $category->allow_early_withdrawal,
                'allow_partial_withdrawal' => $category->allow_partial_withdrawal,
                'early_break_fee_percent' => $category->early_withdrawal_fee_percent,
                'early_break_interest_forfeit_percent' => $category->early_withdrawal_interest_forfeited_percent,
                'maturity_fee_percent' => $category->maturity_fee_percent,
                'compounding_frequency' => 'daily',
                'status' => 'active',
                'metadata' => [
                    'category_name' => $category->name,
                    'initial_deposit_usd' => $amountUsd,
                    'rate_ngn_per_usd' => $currency === 'NGN' ? $this->getNgnPerUsd() : null,
                ],
            ]);

            // Record initial savings contribution
            SavingsContribution::create([
                'savings_plan_id' => $plan->id,
                'user_id' => $lockedUser->id,
                'reference' => $txReference,
                'type' => 'deposit',
                'amount' => $initialDeposit,
                'principal_before' => 0.00000000,
                'principal_after' => $initialDeposit,
                'description' => "Initial deposit of " . ($currency === 'USD' ? '$' : '₦') . number_format($initialDeposit, 2) . " into {$plan->name}",
                'status' => 'completed',
                'metadata' => [
                    'amount_usd' => $amountUsd,
                ],
            ]);

            SavingsAuditLog::record(
                $plan->id,
                $lockedUser->id,
                $lockedUser->id,
                'plan_created',
                null,
                [
                    'reference' => $plan->reference,
                    'principal' => $initialDeposit,
                    'currency' => $currency,
                    'annual_rate' => $plan->annual_interest_rate,
                    'duration_days' => $durationDays,
                ]
            );

            return $plan;
        });
    }

    /**
     * Add extra money / top-up an existing active savings plan.
     */
    public function addContribution(SavingsPlan $plan, User $user, float $amount): SavingsContribution
    {
        if (! $plan->isActive()) {
            throw new InvalidArgumentException('Contributions can only be made to active savings plans.');
        }

        if ($amount <= 0) {
            throw new InvalidArgumentException('Contribution amount must be greater than zero.');
        }

        $currency = $plan->currency;
        $amountUsd = $this->convertToUsd($amount, $currency);

        return DB::transaction(function () use ($plan, $user, $amount, $currency, $amountUsd) {
            $lockedUser = User::lockForUpdate()->findOrFail($user->id);
            $lockedPlan = SavingsPlan::lockForUpdate()->findOrFail($plan->id);

            if (! $lockedPlan->isActive()) {
                throw new InvalidArgumentException('Plan is no longer active.');
            }

            if ((float) $lockedUser->walletBalance < $amountUsd) {
                throw new RuntimeException('Insufficient funds in your main wallet for this top-up.');
            }

            $txReference = 'SSS-TOP-' . strtoupper(Str::random(12));

            // Debit main wallet
            $this->walletService->debit(
                $lockedUser,
                $amountUsd,
                'savings_topup',
                $txReference,
                [
                    'plan_reference' => $lockedPlan->reference,
                    'plan_name' => $lockedPlan->name,
                    'amount_local' => $amount,
                    'currency' => $currency,
                ]
            );

            $before = (float) $lockedPlan->principal_amount;
            $after = round($before + $amount, 8);

            $lockedPlan->principal_amount = $after;
            $lockedPlan->save();

            $contribution = SavingsContribution::create([
                'savings_plan_id' => $lockedPlan->id,
                'user_id' => $lockedUser->id,
                'reference' => $txReference,
                'type' => 'additional_deposit',
                'amount' => $amount,
                'principal_before' => $before,
                'principal_after' => $after,
                'description' => "Additional contribution of " . ($currency === 'USD' ? '$' : '₦') . number_format($amount, 2) . " to {$lockedPlan->name}",
                'status' => 'completed',
                'metadata' => [
                    'amount_usd' => $amountUsd,
                ],
            ]);

            SavingsAuditLog::record(
                $lockedPlan->id,
                $lockedUser->id,
                $lockedUser->id,
                'deposit_made',
                ['principal' => $before],
                ['principal' => $after, 'added' => $amount]
            );

            return $contribution;
        });
    }

    /**
     * Accrue daily interest for a single plan on a given date.
     * Convention: Daily interest = Eligible Principal * (Annual Rate / 100) / 365 (or 366 in leap year)
     * Fully idempotent.
     */
    public function accrueInterestForPlan(SavingsPlan $plan, ?Carbon $date = null): ?SavingsInterestAccrual
    {
        $date = $date ? $date->copy()->startOfDay() : Carbon::yesterday()->startOfDay();
        $dateStr = $date->toDateString();

        if (! $plan->isActive()) {
            return null;
        }

        // Must be on or after commencement date
        if ($date->lt(Carbon::parse($plan->interest_commencement_date)->startOfDay())) {
            return null;
        }

        // Check if already accrued for this date (Idempotency check)
        $alreadyAccrued = SavingsInterestAccrual::where('savings_plan_id', $plan->id)
            ->whereDate('accrual_date', $dateStr)
            ->exists();

        if ($alreadyAccrued) {
            return null;
        }

        $principal = (float) $plan->principal_amount;
        if ($principal <= 0) {
            return null;
        }

        $rate = (float) $plan->annual_interest_rate;
        if ($rate <= 0) {
            return null; // Pure savings without interest
        }

        $isLeap = $date->isLeapYear();
        $divisor = $isLeap ? 366.0 : 365.0;

        // Daily interest calculation with high precision
        $dayInterest = round(($principal * ($rate / 100.0)) / $divisor, 8);

        return DB::transaction(function () use ($plan, $dateStr, $principal, $rate, $dayInterest, $isLeap) {
            $lockedPlan = SavingsPlan::lockForUpdate()->findOrFail($plan->id);

            // Re-check inside lock
            $exists = SavingsInterestAccrual::where('savings_plan_id', $lockedPlan->id)
                ->whereDate('accrual_date', $dateStr)
                ->exists();

            if ($exists) {
                return null;
            }

            $cumBefore = (float) $lockedPlan->accrued_interest;
            $cumAfter = round($cumBefore + $dayInterest, 8);

            $accrual = SavingsInterestAccrual::create([
                'savings_plan_id' => $lockedPlan->id,
                'accrual_date' => $dateStr,
                'eligible_principal' => $principal,
                'annual_rate' => $rate,
                'day_interest_amount' => $dayInterest,
                'cumulative_interest_before' => $cumBefore,
                'cumulative_interest_after' => $cumAfter,
                'is_leap_year' => $isLeap,
                'created_at' => now(),
            ]);

            $lockedPlan->accrued_interest = $cumAfter;
            $lockedPlan->last_accrual_date = $dateStr;
            $lockedPlan->save();

            SavingsAuditLog::record(
                $lockedPlan->id,
                $lockedPlan->user_id,
                null, // system
                'interest_accrued',
                ['accrued_interest' => $cumBefore],
                ['accrued_interest' => $cumAfter, 'day_interest' => $dayInterest, 'date' => $dateStr]
            );

            return $accrual;
        });
    }

    /**
     * Break / early withdrawal for a plan.
     */
    public function breakPlan(SavingsPlan $plan, User $user): array
    {
        if ($plan->user_id !== $user->id && ! $user->is_admin) {
            throw new RuntimeException('Unauthorized action.');
        }

        if (! $plan->isActive()) {
            throw new InvalidArgumentException('Only active savings plans can be broken.');
        }

        if (! $plan->allow_early_withdrawal) {
            throw new InvalidArgumentException('Early withdrawal is locked for this savings plan category.');
        }

        return DB::transaction(function () use ($plan, $user) {
            $lockedPlan = SavingsPlan::lockForUpdate()->findOrFail($plan->id);
            $lockedUser = User::lockForUpdate()->findOrFail($lockedPlan->user_id);

            if (! $lockedPlan->isActive()) {
                throw new InvalidArgumentException('Plan is already closed or processed.');
            }

            // If the plan has already reached or passed maturity, use existing maturity withdrawal rules
            if ($lockedPlan->isMatured()) {
                return $this->processMaturity($lockedPlan);
            }

            $principal = (float) $lockedPlan->principal_amount;
            if ($principal <= 0) {
                throw new InvalidArgumentException('No eligible savings principal available to withdraw.');
            }

            // Recalculate fee, forfeited interest, and net payout on server under lock
            $summary = $lockedPlan->calculateEarlyBreakSummary();
            $netPayout = (float) $summary['net_payout'];
            $earlyBreakFee = (float) $summary['early_break_fee'];
            $forfeitedInterest = (float) $summary['forfeited_interest'];
            $feePercent = (float) $summary['early_break_fee_percent'];
            $currency = $lockedPlan->currency;

            if ($netPayout < 0) {
                throw new InvalidArgumentException('Invalid net payout calculated.');
            }

            $payoutUsd = $this->convertToUsd($netPayout, $currency);
            $txRef = 'SSS-BRK-' . strtoupper(Str::random(12));

            // Credit net payout to main wallet atomically
            if ($payoutUsd > 0) {
                $this->walletService->credit(
                    $lockedUser,
                    $payoutUsd,
                    'savings_break',
                    $txRef,
                    [
                        'savings_plan_id' => $lockedPlan->id,
                        'user_id' => $lockedUser->id,
                        'plan_reference' => $lockedPlan->reference,
                        'plan_name' => $lockedPlan->name,
                        'principal' => $principal,
                        'early_break_fee' => $earlyBreakFee,
                        'early_break_fee_percent' => $feePercent,
                        'forfeited_interest' => $forfeitedInterest,
                        'net_payout' => $netPayout,
                        'currency' => $currency,
                        'transaction_reference' => $txRef,
                        'timestamp' => now()->toIso8601String(),
                    ]
                );
            }

            $statement = [
                'type' => 'early_withdrawal',
                'savings_plan_id' => $lockedPlan->id,
                'user_id' => $lockedUser->id,
                'plan_name' => $lockedPlan->name,
                'principal' => $principal,
                'early_break_fee' => $earlyBreakFee,
                'early_break_fee_percent' => $feePercent,
                'gross_interest_earned' => (float) $lockedPlan->accrued_interest,
                'forfeited_interest' => $forfeitedInterest,
                'net_payout' => $netPayout,
                'currency' => $currency,
                'transaction_reference' => $txRef,
                'processed_at' => now()->toIso8601String(),
                'wallet_tx_ref' => $txRef,
                'payout_usd' => $payoutUsd,
            ];

            // Close savings balance and mark plan as broken atomically
            $lockedPlan->status = 'broken';
            $lockedPlan->principal_amount = 0.00000000;
            $lockedPlan->broken_at = now();
            $lockedPlan->closed_at = now();
            $lockedPlan->forfeited_interest = $forfeitedInterest;
            $lockedPlan->total_withdrawn = $netPayout;
            $lockedPlan->payout_statement = $statement;
            $lockedPlan->save();

            SavingsContribution::create([
                'savings_plan_id' => $lockedPlan->id,
                'user_id' => $lockedUser->id,
                'reference' => $txRef,
                'type' => 'early_withdrawal',
                'amount' => $netPayout,
                'principal_before' => $principal,
                'principal_after' => 0.00000000,
                'description' => "Early withdrawal payout of " . ($currency === 'USD' ? '$' : '₦') . number_format($netPayout, 2) . " to main wallet (Early break fee: " . ($currency === 'USD' ? '$' : '₦') . number_format($earlyBreakFee, 2) . ")",
                'status' => 'completed',
                'metadata' => $statement,
            ]);

            SavingsAuditLog::record(
                $lockedPlan->id,
                $lockedUser->id,
                $user->id,
                'early_withdrawal_executed',
                [
                    'status' => 'active',
                    'principal' => $principal,
                    'accrued_interest' => (float) $lockedPlan->accrued_interest,
                ],
                [
                    'status' => 'broken',
                    'principal' => $principal,
                    'early_break_fee' => $earlyBreakFee,
                    'early_break_fee_percent' => $feePercent,
                    'forfeited_interest' => $forfeitedInterest,
                    'net_payout' => $netPayout,
                    'transaction_reference' => $txRef,
                    'timestamp' => now()->toIso8601String(),
                ]
            );

            return [
                'success' => true,
                'summary' => $summary,
                'statement' => $statement,
                'wallet_balance_usd' => (float) $lockedUser->walletBalance,
            ];
        });
    }

    /**
     * Process maturity payout when plan completes its term.
     * Payout = Principal + (Accrued Interest - Maturity Fee)
     */
    public function processMaturity(SavingsPlan $plan): array
    {
        if (! $plan->isActive()) {
            throw new InvalidArgumentException('Plan is not active.');
        }

        return DB::transaction(function () use ($plan) {
            $lockedPlan = SavingsPlan::lockForUpdate()->findOrFail($plan->id);
            $lockedUser = User::lockForUpdate()->findOrFail($lockedPlan->user_id);

            if (! $lockedPlan->isActive()) {
                throw new InvalidArgumentException('Plan already completed.');
            }

            $principal = (float) $lockedPlan->principal_amount;
            $earnedInterest = (float) $lockedPlan->accrued_interest;
            $maturityFeePercent = (float) $lockedPlan->maturity_fee_percent;

            // Maturity fee is 1.5% of EARNED INTEREST (not principal)
            $maturityFee = round($earnedInterest * ($maturityFeePercent / 100.0), 4);
            $netInterest = max(0.0, $earnedInterest - $maturityFee);
            $totalPayout = round($principal + $netInterest, 2);
            $currency = $lockedPlan->currency;

            $payoutUsd = $this->convertToUsd($totalPayout, $currency);
            $txRef = 'SSS-MAT-' . strtoupper(Str::random(12));

            // Credit user wallet atomically
            if ($payoutUsd > 0) {
                $this->walletService->credit(
                    $lockedUser,
                    $payoutUsd,
                    'savings_maturity',
                    $txRef,
                    [
                        'plan_reference' => $lockedPlan->reference,
                        'plan_name' => $lockedPlan->name,
                        'principal' => $principal,
                        'net_interest' => $netInterest,
                        'maturity_fee' => $maturityFee,
                        'total_payout' => $totalPayout,
                        'currency' => $currency,
                    ]
                );
            }

            $statement = [
                'type' => 'maturity_payout',
                'plan_name' => $lockedPlan->name,
                'principal' => $principal,
                'gross_interest_earned' => $earnedInterest,
                'maturity_fee' => $maturityFee,
                'maturity_fee_percent' => $maturityFeePercent,
                'net_interest' => $netInterest,
                'total_payout' => $totalPayout,
                'currency' => $currency,
                'processed_at' => now()->toIso8601String(),
                'wallet_tx_ref' => $txRef,
                'payout_usd' => $payoutUsd,
            ];

            $lockedPlan->status = 'matured';
            $lockedPlan->principal_amount = 0.00000000;
            $lockedPlan->matured_at = now();
            $lockedPlan->closed_at = now();
            $lockedPlan->paid_interest = $netInterest;
            $lockedPlan->total_withdrawn = $totalPayout;
            $lockedPlan->payout_statement = $statement;
            $lockedPlan->save();

            SavingsContribution::create([
                'savings_plan_id' => $lockedPlan->id,
                'user_id' => $lockedUser->id,
                'reference' => $txRef,
                'type' => 'maturity_payout',
                'amount' => $totalPayout,
                'principal_before' => $principal,
                'principal_after' => 0.00000000,
                'description' => "Maturity payout of " . ($currency === 'USD' ? '$' : '₦') . number_format($totalPayout, 2) . " (Principal: " . number_format($principal, 2) . " + Net Interest: " . number_format($netInterest, 2) . ")",
                'status' => 'completed',
                'metadata' => $statement,
            ]);

            SavingsAuditLog::record(
                $lockedPlan->id,
                $lockedUser->id,
                null,
                'maturity_executed',
                ['status' => 'active', 'principal' => $principal],
                ['status' => 'matured', 'total_payout' => $totalPayout, 'net_interest' => $netInterest]
            );

            return [
                'success' => true,
                'statement' => $statement,
                'wallet_balance_usd' => (float) $lockedUser->walletBalance,
            ];
        });
    }
}
