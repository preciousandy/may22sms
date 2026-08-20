<?php

namespace App\Console\Commands;

use App\Models\BoostService;
use App\Models\Setting;
use App\Services\JustAnotherPanelService;
use Illuminate\Console\Command;

class SyncBoostServices extends Command
{
    protected $signature = 'boost:sync-services {--force : Run even if auto sync is disabled}';

    protected $description = 'Sync boost services from JustAnotherPanel';

    public function handle(JustAnotherPanelService $jap): int
    {
        $enabled = (string) (Setting::where('key', 'boost_auto_sync_enabled')->value('value') ?? '0');
        if ($enabled !== '1' && !$this->option('force')) {
            $this->warn('Auto sync is disabled. Enable it in Admin → Manage Boosting, or run: php artisan boost:sync-services --force');
            return self::SUCCESS;
        }

        $servicesData = $jap->getServices();

        if (!is_array($servicesData) || empty($servicesData)) {
            $this->error('No services returned from provider. Check JAP_API_KEY and JAP_API_URL in your .env.');
            return self::FAILURE;
        }

        $count = 0;
        foreach ($servicesData as $row) {
            if (!is_array($row)) {
                continue;
            }

            $japServiceId = $row['service'] ?? $row['id'] ?? null;
            if (!$japServiceId) {
                continue;
            }

            $service = BoostService::firstOrNew(['jap_service_id' => (int) $japServiceId]);
            if (!$service->exists) {
                $service->is_active = true;
            }

            $service->name = (string) ($row['name'] ?? $service->name ?? '');
            $service->category = (string) ($row['category'] ?? $service->category ?? 'Other');
            $service->type = (string) ($row['type'] ?? $service->type ?? '');
            $service->rate = (float) ($row['rate'] ?? $service->rate ?? 0);
            $service->min = (int) ($row['min'] ?? $service->min ?? 0);
            $service->max = (int) ($row['max'] ?? $service->max ?? 0);
            $service->save();

            $count++;
        }

        Setting::updateOrCreate(
            ['key' => 'boost_auto_sync_last_run'],
            ['value' => now()->toDateTimeString()]
        );

        $this->info("Synced {$count} boost services.");

        return self::SUCCESS;
    }
}
