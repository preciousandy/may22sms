<?php

namespace App\Console\Commands;

use App\Services\SeasonalEngineService;
use Illuminate\Console\Command;

class SyncSeasonalSchedule extends Command
{
    protected $signature = 'seasonal:sync-schedule';

    protected $description = 'Automatically activate and deactivate scheduled MAY22SMS seasonal celebration modes.';

    public function handle(SeasonalEngineService $engine): int
    {
        $result = $engine->syncScheduledModes();

        if (! empty($result['activated'])) {
            $this->info('Activated seasonal modes: ' . implode(', ', $result['activated']));
        }

        if (! empty($result['deactivated'])) {
            $this->info('Deactivated expired seasonal modes: ' . implode(', ', $result['deactivated']));
        }

        if (empty($result['activated']) && empty($result['deactivated'])) {
            $this->line('No seasonal schedule state changes required.');
        }

        return self::SUCCESS;
    }
}

