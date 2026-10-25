<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\UtilityTransaction;
use App\Services\BigiSubService;
use App\Services\UtilityService;
use Illuminate\Http\Request;
use Throwable;

class UtilityAdminController extends Controller
{
    protected UtilityService $utilityService;
    protected BigiSubService $bigiSubService;

    public function __construct(UtilityService $utilityService, BigiSubService $bigiSubService)
    {
        $this->utilityService = $utilityService;
        $this->bigiSubService = $bigiSubService;
    }

    /**
     * Transactions overview.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $type = $request->query('type');
        $status = $request->query('status');

        $query = UtilityTransaction::with('user')->latest();

        if ($type) {
            $query->where('service_type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($q !== '') {
            $query->where(function ($sq) use ($q) {
                $sq->where('reference', 'like', "%{$q}%")
                    ->orWhere('provider_reference', 'like', "%{$q}%")
                    ->orWhere('customer_target', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%")
                    ->orWhere('token', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    });
            });
        }

        $transactions = $query->paginate(20)->withQueryString();

        // Metrics
        $totalVolumeNgn = UtilityTransaction::where('status', 'completed')->sum('amount_ngn');
        $totalSuccessful = UtilityTransaction::where('status', 'completed')->count();
        $totalFailed = UtilityTransaction::whereIn('status', ['failed', 'refunded'])->count();
        $totalPending = UtilityTransaction::whereIn('status', ['pending', 'uncertain'])->count();

        // Settings snapshot
        $apiToken = Setting::where('key', 'bigisub_api_token')->value('value') ?: config('services.bigisub.api_token', '');
        $pin = Setting::where('key', 'bigisub_pin')->value('value') ?: config('services.bigisub.pin', '1234');
        $baseUrl = Setting::where('key', 'bigisub_base_url')->value('value') ?: config('services.bigisub.base_url', 'https://api.bigisub.ng');
        $simulate = (int) (Setting::where('key', 'bigisub_simulate')->value('value') ?? (config('services.bigisub.simulate') ? 1 : 0)) === 1;
        $airtimeDiscount = (float) (Setting::where('key', 'utility_airtime_discount_percent')->value('value') ?? 2.0);

        $providerBalance = $this->bigiSubService->getBalance();

        return view('admin.utilities.index', compact(
            'transactions',
            'q',
            'type',
            'status',
            'totalVolumeNgn',
            'totalSuccessful',
            'totalFailed',
            'totalPending',
            'apiToken',
            'pin',
            'baseUrl',
            'simulate',
            'airtimeDiscount',
            'providerBalance'
        ));
    }

    /**
     * Inspect single transaction.
     */
    public function show(int $id)
    {
        $tx = UtilityTransaction::with('user')->findOrFail($id);

        return view('admin.utilities.show', compact('tx'));
    }

    /**
     * Save BigiSub and utility configuration settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'bigisub_api_token' => 'nullable|string|max:255',
            'bigisub_pin' => 'nullable|string|max:10',
            'bigisub_base_url' => 'required|url|max:255',
            'bigisub_simulate' => 'nullable|in:0,1',
            'utility_airtime_discount_percent' => 'required|numeric|min:0|max:20',
        ]);

        Setting::updateOrCreate(
            ['key' => 'bigisub_api_token'],
            ['value' => trim((string) $request->input('bigisub_api_token', ''))]
        );

        Setting::updateOrCreate(
            ['key' => 'bigisub_pin'],
            ['value' => trim((string) $request->input('bigisub_pin', ''))]
        );

        Setting::updateOrCreate(
            ['key' => 'bigisub_base_url'],
            ['value' => rtrim(trim((string) $request->input('bigisub_base_url')), '/')]
        );

        Setting::updateOrCreate(
            ['key' => 'bigisub_simulate'],
            ['value' => $request->has('bigisub_simulate') ? '1' : '0']
        );

        Setting::updateOrCreate(
            ['key' => 'utility_airtime_discount_percent'],
            ['value' => (string) $request->input('utility_airtime_discount_percent', '2.0')]
        );

        return back()->with('success', 'BigiSub & Utility settings saved successfully.');
    }

    /**
     * Reconcile status with BigiSub.
     */
    public function reconcile(int $id)
    {
        $tx = UtilityTransaction::findOrFail($id);

        try {
            $updated = $this->utilityService->reconcileTransaction($tx);

            return back()->with('success', "Transaction {$updated->reference} reconciled. Status: {$updated->status}");
        } catch (Throwable $e) {
            return back()->with('error', 'Reconciliation failed: ' . $e->getMessage());
        }
    }

    /**
     * Manually refund transaction to user wallet.
     */
    public function refund(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $tx = UtilityTransaction::findOrFail($id);

        if (!$tx->canRefund()) {
            return back()->with('error', 'This transaction is not eligible for refund.');
        }

        try {
            $reason = $request->input('reason') ?: 'Manual administrative refund by admin #' . auth()->id();
            $this->utilityService->refundTransaction($tx, auth()->id(), $reason);

            return back()->with('success', "Refund of ₦" . number_format($tx->amount_ngn, 2) . " ($" . number_format($tx->total_charged_usd, 4) . ") credited to {$tx->user->name}'s wallet successfully.");
        } catch (Throwable $e) {
            return back()->with('error', 'Refund failed: ' . $e->getMessage());
        }
    }
}
