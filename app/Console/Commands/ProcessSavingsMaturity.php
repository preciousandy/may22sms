<?php

namespace App\Console\Commands;

use App\Models\SavingsPlan;
use App\Services\SavingsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class ProcessSavingsMaturity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'savings:process-maturity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automatic payout and completion for matured Save Small Small plans';

    /**
     * Execute the console command.
     */
    public function handle(SavingsService $savingsService): int
    {
        $today = Carbon::today()->toDateString();
        $this->info("Checking for matured Save Small Small plans on or before: {$today}");

        $maturedPlans = SavingsPlan::where('status', 'active')
            ->whereDate('maturity_date', '<=', $today)
            ->get();

        $this->info("Found {$maturedPlans->count()} matured plans to process.");

        $processed = 0;
        $failed = 0;

        foreach ($maturedPlans as $plan) {
            try {
                $result = $savingsService->processMaturity($plan);
                $payout = $result['statement']['total_payout'] ?? 0;
                $this->line("  ✓ Payout processed for Plan #{$plan->id} ({$plan->reference}): Paid {$plan->currency} {$payout}");
                $processed++;
            } catch (Throwable $e) {
                $failed++;
                $this->error("  ✗ Failed to process maturity for Plan #{$plan->id}: " . $e->getMessage());
            }
        }

        $this->info("Maturity Processing Complete. Successfully Payouts: {$processed}, Failures: {$failed}");

        return 0;
    }
}
