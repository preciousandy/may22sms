<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('boost:sync-services')->hourly()->withoutOverlapping();
        $schedule->command('app:sync-football-matches --days=7')->daily()->withoutOverlapping();
        $schedule->command('savings:accrue-interest')->dailyAt('00:05')->withoutOverlapping();
        $schedule->command('savings:process-maturity')->dailyAt('00:15')->withoutOverlapping();
        $schedule->command('seasonal:sync-schedule')->everyMinute()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
