<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoostOrder;
use App\Models\BoostService;
use App\Models\Setting;
use App\Services\JustAnotherPanelService;
use Illuminate\Http\Request;

class BoostController extends Controller
{
    public function index(Request $request)
    {
        $markup = (float) (Setting::where('key', 'boost_markup_percent')->value('value') ?? 20);
        $categoryMarkups = (string) (Setting::where('key', 'boost_markup_by_category')->value('value') ?? '');
        $boostMarkupByCategory = [];
        if ($categoryMarkups !== '') {
            $decoded = json_decode($categoryMarkups, true);
            if (is_array($decoded)) {
                foreach ($decoded as $k => $v) {
                    if (is_string($k) && is_numeric($v)) {
                        $boostMarkupByCategory[(string) $k] = (float) $v;
                    }
                }
            }
        }

        $categories = BoostService::query()
            ->select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter(fn ($v) => (string) $v !== '')
            ->values()
            ->toArray();
        $autoSyncEnabled = (string) (Setting::where('key', 'boost_auto_sync_enabled')->value('value') ?? '0');
        $autoSyncLastRun = Setting::where('key', 'boost_auto_sync_last_run')->value('value');

        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');

        $servicesQuery = BoostService::query()
            ->orderByRaw('fail_count DESC, last_failed_at IS NULL ASC, last_failed_at DESC')
            ->orderBy('category')
            ->orderBy('name');

        if ($q !== '') {
            $servicesQuery->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%")
                    ->orWhere('type', 'like', "%{$q}%")
                    ->orWhere('last_error', 'like', "%{$q}%");

                if (is_numeric($q)) {
                    $query->orWhere('jap_service_id', (int) $q);
                }
            });
        }

        if ($status === 'active') {
            $servicesQuery->where('is_active', true);
        } elseif ($status === 'inactive') {
            $servicesQuery->where('is_active', false);
        } elseif ($status === 'failing') {
            $servicesQuery->where('fail_count', '>', 0);
        }

        $services = $servicesQuery->paginate(50)->withQueryString();

        $orders = BoostOrder::with(['user', 'service'])->latest()->take(25)->get();

        $failingCount = BoostService::where('fail_count', '>', 0)->count();

        return view('admin.boosting.index', compact('markup', 'services', 'orders', 'autoSyncEnabled', 'autoSyncLastRun', 'q', 'status', 'categories', 'boostMarkupByCategory', 'failingCount'));
    }

    public function updateMarkup(Request $request)
    {
        $request->validate([
            'boost_markup_percent' => 'required|numeric|min:0',
        ]);

        Setting::updateOrCreate(
            ['key' => 'boost_markup_percent'],
            ['value' => $request->boost_markup_percent]
        );

        return back()->with('success', 'Boost pricing settings updated.');
    }

    public function updateAutoSync(Request $request)
    {
        $request->validate([
            'boost_auto_sync_enabled' => 'nullable|in:1',
        ]);

        Setting::updateOrCreate(
            ['key' => 'boost_auto_sync_enabled'],
            ['value' => $request->has('boost_auto_sync_enabled') ? '1' : '0']
        );

        return back()->with('success', 'Auto sync setting updated.');
    }

    public function syncServices(JustAnotherPanelService $jap)
    {
        $servicesData = $jap->getServices();

        if (! is_array($servicesData) || empty($servicesData)) {
            return back()->withErrors(['sync' => 'Failed to fetch services from provider. Check API key or URL.']);
        }

        $count = 0;
        foreach ($servicesData as $row) {
            if (! is_array($row)) {
                continue;
            }

            $japServiceId = $row['service'] ?? $row['id'] ?? null;
            if (! $japServiceId) {
                continue;
            }

            $service = BoostService::firstOrNew(['jap_service_id' => (int) $japServiceId]);
            if (! $service->exists) {
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

        return back()->with('success', "Synced {$count} boost services.");
    }

    public function updateService(Request $request, $id)
    {
        $request->validate([
            'price_override' => 'nullable|numeric|min:0',
        ]);

        $service = BoostService::findOrFail($id);
        $wasActive = $service->is_active;
        $service->is_active = $request->has('is_active');
        $service->price_override = $request->price_override !== null && $request->price_override !== ''
            ? (float) $request->price_override
            : null;

        if (! $wasActive && $service->is_active) {
            $service->fail_count = 0;
            $service->last_error = null;
            $service->last_failed_at = null;
        }

        $service->save();

        return back()->with('success', 'Service updated.');
    }

    public function resetServiceFailures($id)
    {
        $service = BoostService::findOrFail($id);
        $service->resetFailureStats();

        return back()->with('success', "Failure stats reset for: {$service->name}.");
    }

    public function bulkResetFailures()
    {
        BoostService::where('fail_count', '>', 0)->update([
            'fail_count' => 0,
            'last_error' => null,
            'last_failed_at' => null,
        ]);

        return back()->with('success', 'All failure statistics have been reset.');
    }

    public function updateCategoryMarkups(Request $request)
    {
        $raw = (string) $request->input('category_markups', '');
        $lines = explode("\n", str_replace("\r", '', $raw));
        $map = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode('=', $line, 2));
            if (count($parts) !== 2) {
                continue;
            }

            $category = (string) ($parts[0] ?? '');
            $percentRaw = (string) ($parts[1] ?? '');
            if ($category === '' || ! is_numeric($percentRaw)) {
                continue;
            }

            $percent = (float) $percentRaw;
            if ($percent < 0) {
                $percent = 0;
            }

            $map[$category] = $percent;
        }

        Setting::updateOrCreate(
            ['key' => 'boost_markup_by_category'],
            ['value' => json_encode($map)]
        );

        return back()->with('success', 'Category markups updated.');
    }
}
