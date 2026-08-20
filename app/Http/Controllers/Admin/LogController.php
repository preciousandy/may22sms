<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogCredential;
use App\Models\LogItem;
use App\Models\SystemTransaction;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
        $logItems = LogItem::withCount(['credentials as available_stock' => function ($query) {
            $query->where('is_sold', false);
        }])->latest()->get();

        return view('admin.logs.index', compact('logItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'platform' => 'required|string|max:100',
            'price' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);

        $item = LogItem::create($request->only(['title', 'platform', 'price', 'description']));

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'type' => 'log_item',
            'action' => 'create',
            'status' => 'completed',
            'subject_type' => LogItem::class,
            'subject_id' => (string) $item->id,
            'after' => [
                'title' => $item->title,
                'platform' => $item->platform,
                'price' => (float) $item->price,
            ],
        ]);

        return back()->with('success', 'Log category created successfully.');
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'bulk_items' => 'nullable|string',
            'bulk_file' => 'nullable|file|max:2048|mimes:txt,csv',
            'default_platform' => 'nullable|string|max:100',
            'default_price' => 'nullable|numeric|min:0.01',
            'default_description' => 'nullable|string',
        ]);

        $allowedPlatforms = [
            'facebook',
            'instagram',
            'twitter',
            'youtube',
            'tiktok',
            'discord',
            'other',
        ];

        $raw = (string) $request->input('bulk_items', '');
        if ($request->hasFile('bulk_file')) {
            $path = $request->file('bulk_file')->getRealPath();
            if (is_string($path) && $path !== '' && file_exists($path)) {
                $raw = (string) file_get_contents($path);
            }
        }

        if (trim($raw) === '') {
            return back()->withErrors(new \Illuminate\Support\MessageBag(['Paste items or upload a file.']));
        }

        $defaultPlatform = strtolower(trim((string) $request->input('default_platform', '')));
        $defaultPrice = $request->filled('default_price') ? (float) $request->input('default_price') : null;
        $defaultDescription = trim((string) $request->input('default_description', ''));

        $platformAliases = [
            'fb' => 'facebook',
            'facebook' => 'facebook',
            'ig' => 'instagram',
            'insta' => 'instagram',
            'instagram' => 'instagram',
            'x' => 'twitter',
            'twitter' => 'twitter',
            'twitter/x' => 'twitter',
            'youtube' => 'youtube',
            'yt' => 'youtube',
            'tiktok' => 'tiktok',
            'tt' => 'tiktok',
            'discord' => 'discord',
            'other' => 'other',
        ];

        $lines = preg_split("/\n/", str_replace("\r", "\n", $raw));
        $created = 0;
        $errors = [];

        foreach (($lines ?: []) as $i => $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }
            if (str_starts_with($line, '#') || str_starts_with($line, '//')) {
                continue;
            }

            $parts = null;
            if (str_contains($line, '|')) {
                $parts = array_map('trim', explode('|', $line, 4));
            } else {
                $csv = array_map('trim', str_getcsv($line));
                if (count($csv) >= 2) {
                    $parts = $csv;
                } else {
                    $tabs = array_map('trim', preg_split("/\t+/", $line) ?: []);
                    $parts = $tabs;
                }
            }

            $parts = array_values(array_filter($parts ?? [], fn ($v) => $v !== null));

            if ($i === 0) {
                $joined = strtolower(implode(',', $parts));
                if (str_contains($joined, 'title') && str_contains($joined, 'platform') && str_contains($joined, 'price')) {
                    continue;
                }
            }

            $title = trim((string) ($parts[0] ?? ''));
            $platform = strtolower(trim((string) ($parts[1] ?? '')));
            $priceRaw = trim((string) ($parts[2] ?? ''));
            $description = trim((string) ($parts[3] ?? ''));

            if (count($parts) === 1) {
                $platform = $defaultPlatform;
                $priceRaw = $defaultPrice !== null ? (string) $defaultPrice : '';
                $description = $defaultDescription;
            } elseif (count($parts) === 2) {
                $platform = $defaultPlatform;
                $priceRaw = (string) ($parts[1] ?? '');
                $description = $defaultDescription;
            }

            $platform = preg_replace('/\s+/', '', $platform) ?: '';
            $platform = $platformAliases[$platform] ?? $platform;

            if ($title === '') {
                $errors[] = 'Line ' . ($i + 1) . ': title is required';
                continue;
            }
            if ($platform === '' || ! in_array($platform, $allowedPlatforms, true)) {
                $errors[] = 'Line ' . ($i + 1) . ': invalid platform (' . $platform . ')';
                continue;
            }
            if (! is_numeric($priceRaw) || (float) $priceRaw < 0.01) {
                $errors[] = 'Line ' . ($i + 1) . ': invalid price (' . $priceRaw . ')';
                continue;
            }

            LogItem::create([
                'title' => $title,
                'platform' => $platform,
                'price' => (float) $priceRaw,
                'description' => $description !== '' ? $description : null,
                'is_active' => true,
                'is_out_of_stock' => true,
            ]);
            $created++;
        }

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'type' => 'log_item',
            'action' => 'bulk_create',
            'status' => $created > 0 ? 'completed' : 'failed',
            'meta' => [
                'created' => $created,
                'errors' => $errors,
            ],
        ]);

        $response = back()->with('success', 'Created ' . $created . ' log items.');

        if (! empty($errors)) {
            return $response->withErrors(new \Illuminate\Support\MessageBag($errors));
        }

        return $response;
    }

    public function updateStatus(Request $request, $id)
    {
        $logItem = LogItem::findOrFail($id);
        $beforeActive = (bool) ($logItem->is_active ?? false);
        $logItem->is_active = $request->has('is_active');
        $logItem->save();

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'type' => 'log_item',
            'action' => 'update_status',
            'status' => 'completed',
            'subject_type' => LogItem::class,
            'subject_id' => (string) $logItem->id,
            'before' => [
                'is_active' => $beforeActive,
            ],
            'after' => [
                'is_active' => (bool) $logItem->is_active,
            ],
        ]);

        return back()->with('success', 'Status updated.');
    }

    public function updateItem(Request $request, $id)
    {
        $logItem = LogItem::findOrFail($id);
        $before = [
            'title' => $logItem->title,
            'platform' => $logItem->platform,
            'price' => (float) $logItem->price,
            'description' => $logItem->description,
        ];

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'platform' => 'required|string|max:100',
            'price' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);

        $logItem->title = $validated['title'];
        $logItem->platform = strtolower((string) $validated['platform']);
        $logItem->price = (float) $validated['price'];
        $logItem->description = $validated['description'] ?? null;
        $logItem->save();

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'type' => 'log_item',
            'action' => 'update',
            'status' => 'completed',
            'subject_type' => LogItem::class,
            'subject_id' => (string) $logItem->id,
            'before' => $before,
            'after' => [
                'title' => $logItem->title,
                'platform' => $logItem->platform,
                'price' => (float) $logItem->price,
                'description' => $logItem->description,
            ],
        ]);

        return back()->with('success', 'Log item updated.');
    }

    public function addStock(Request $request, $id)
    {
        $request->validate([
            'credentials' => 'nullable|string',
            'credentials_file' => 'nullable|file|max:5120|mimes:txt',
            'dedupe' => 'nullable|in:0,1',
        ]);

        $logItem = LogItem::findOrFail($id);
        $beforeOutOfStock = (bool) ($logItem->is_out_of_stock ?? false);
        $logItem->is_out_of_stock = false;
        $logItem->save();

        $raw = (string) ($request->input('credentials') ?? '');
        if ($request->hasFile('credentials_file')) {
            $path = $request->file('credentials_file')->getRealPath();
            if (is_string($path) && $path !== '' && file_exists($path)) {
                $raw = (string) file_get_contents($path);
            }
        }
        $raw = str_replace("\r", "\n", $raw);
        if (trim($raw) === '') {
            return back()->withErrors(new \Illuminate\Support\MessageBag(['Paste credentials or upload a .txt file.']));
        }

        $lines = preg_split("/\n+/", $raw) ?: [];
        $lines = array_values(array_filter(array_map(fn ($v) => trim((string) $v), $lines), fn ($v) => $v !== ''));
        if ((string) $request->input('dedupe', '0') === '1') {
            $lines = array_values(array_unique($lines));
        }

        $count = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if (! empty($line)) {
                LogCredential::create([
                    'log_item_id' => $logItem->id,
                    'data' => $line,
                ]);
                $count++;
            }
        }

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'type' => 'log_stock',
            'action' => 'add',
            'status' => 'completed',
            'subject_type' => LogItem::class,
            'subject_id' => (string) $logItem->id,
            'before' => [
                'is_out_of_stock' => $beforeOutOfStock,
            ],
            'after' => [
                'is_out_of_stock' => (bool) $logItem->is_out_of_stock,
            ],
            'meta' => [
                'added' => $count,
            ],
        ]);

        return back()->with('success', "Added {$count} accounts to stock.");
    }

    public function clearStock($id)
    {
        $logItem = LogItem::findOrFail($id);
        $beforeOutOfStock = (bool) ($logItem->is_out_of_stock ?? false);
        $logItem->is_out_of_stock = true;
        $logItem->save();

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'type' => 'log_stock',
            'action' => 'mark_out_of_stock',
            'status' => 'completed',
            'subject_type' => LogItem::class,
            'subject_id' => (string) $logItem->id,
            'before' => [
                'is_out_of_stock' => $beforeOutOfStock,
            ],
            'after' => [
                'is_out_of_stock' => true,
            ],
        ]);

        return back()->with('success', 'Item marked out of stock.');
    }

    public function markInStock($id)
    {
        $logItem = LogItem::findOrFail($id);
        $beforeOutOfStock = (bool) ($logItem->is_out_of_stock ?? false);
        $logItem->is_out_of_stock = false;
        $logItem->save();

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'type' => 'log_stock',
            'action' => 'mark_in_stock',
            'status' => 'completed',
            'subject_type' => LogItem::class,
            'subject_id' => (string) $logItem->id,
            'before' => [
                'is_out_of_stock' => $beforeOutOfStock,
            ],
            'after' => [
                'is_out_of_stock' => false,
            ],
        ]);

        return back()->with('success', 'Item marked in stock.');
    }
}
