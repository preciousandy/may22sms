<?php

namespace App\Console\Commands;

use App\Models\SavingsPlan;
use App\Services\SavingsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class AccrueSavingsInterest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'savings:accrue-interest {--date= : Custom accrual date (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily idempotent interest accrual for active Save Small Small plans';

    /**
     * Execute the console command.
     */
    public function handle(SavingsService $savingsService): int
    {
        $dateInput = $this->option('date');
        $date = $dateInput ? Carbon::parse($dateInput)->startOfDay() : Carbon::yesterday()->startOfDay();

        $this->info("Starting Save Small Small interest accrual for: {$date->toDateString()}");

        $activePlans = SavingsPlan::where('status', 'active')
            ->where('principal_amount', '>', 0)
            ->whereDate('interest_commencement_date', '<=', $date)
            ->get();

        $this->info("Found {$activePlans->count()} candidate active plans.");

        $processed = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($activePlans as $plan) {
            try {
                $accrual = $savingsService->accrueInterestForPlan($plan, $date);
                if ($accrual) {
                    $processed++;
                    $this->line("  ✓ Accrued for Plan #{$plan->id} ({$plan->reference}): +{$accrual->day_interest_amount}");
                } else {
                    $skipped++;
                }
            } catch (Throwable $e) {
                $errors++;
                $this->error("  ✗ Error processing plan #{$plan->id}: " . $e->getMessage());
            }
        }

        $this->info("Interest Accrual Complete. Processed: {$processed}, Skipped (already accrued): {$skipped}, Errors: {$errors}");

        return 0;
    }
}
