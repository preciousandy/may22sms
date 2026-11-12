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
     * Transactions & Profit Overview Dashboard.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $type = $request->query('type');
        $status = $request->query('status');
        $period = $request->query('period', 'all');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = UtilityTransaction::with('user')->latest();

        // Period filter helper
        $applyDateFilter = function ($q) use ($period, $dateFrom, $dateTo) {
            if ($dateFrom) {
                $q->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $q->whereDate('created_at', '<=', $dateTo);
            }
            if (!$dateFrom && !$dateTo) {
                match ($period) {
                    'today' => $q->whereDate('created_at', today()),
                    '7_days' => $q->where('created_at', '>=', now()->subDays(7)),
                    '30_days' => $q->where('created_at', '>=', now()->subDays(30)),
                    'this_month' => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                    default => null,
                };
            }
        };

        $applyDateFilter($query);

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

        // Base scoped query for metrics
        $metricsQuery = UtilityTransaction::query();
        $applyDateFilter($metricsQuery);

        $totalSuccessful = (clone $metricsQuery)->where('status', 'completed')->count();
        $totalFailed = (clone $metricsQuery)->where('status', 'failed')->count();
        $totalRefunded = (clone $metricsQuery)->where('status', 'refunded')->count();
        $totalPending = (clone $metricsQuery)->whereIn('status', ['pending', 'uncertain'])->count();

        // Financial & Profit Aggregates (Realized only from completed transactions)
        $completedScope = (clone $metricsQuery)->where('status', 'completed');
        $totalVolumeNgn = (clone $completedScope)->sum('amount_ngn');
        $totalCustomerChargedNgn = (clone $completedScope)->selectRaw('SUM(amount_ngn + fee_ngn - discount_ngn) as total')->value('total') ?? 0.0;
        $totalProviderCostNgn = (clone $completedScope)->sum('provider_cost_ngn');
        $totalRealizedProfitNgn = (clone $completedScope)->sum('profit_ngn');
        $totalRealizedProfitUsd = (clone $completedScope)->sum('profit_usd');

        // Service Breakdown for Realized Profit
        $dataProfitNgn = (clone $completedScope)->where('service_type', 'data')->sum('profit_ngn');
        $airtimeProfitNgn = (clone $completedScope)->where('service_type', 'airtime')->sum('profit_ngn');
        $electricityProfitNgn = (clone $completedScope)->where('service_type', 'electricity')->sum('profit_ngn');
        $bettingProfitNgn = (clone $completedScope)->where('service_type', 'betting')->sum('profit_ngn');

        // Estimated Pending Profit (transactions in pipeline awaiting final network callback)
        $estimatedPendingProfitNgn = (clone $metricsQuery)->whereIn('status', ['pending', 'uncertain'])->sum('profit_ngn');

        // Settings snapshot
        $pricing = $this->utilityService->getPricingService();
        $pricingSettings = $pricing->getAllSettings();
        $apiToken = Setting::where('key', 'bigisub_api_token')->value('value') ?: config('services.bigisub.api_token', '');
        $pin = Setting::where('key', 'bigisub_pin')->value('value') ?: config('services.bigisub.pin', '1234');
        $baseUrl = Setting::where('key', 'bigisub_base_url')->value('value') ?: config('services.bigisub.base_url', 'https://api.bigisub.ng');
        $simulate = (int) (Setting::where('key', 'bigisub_simulate')->value('value') ?? (config('services.bigisub.simulate') ? 1 : 0)) === 1;

        $providerBalance = $this->bigiSubService->getBalance();
        $auditLogs = DB::table('utility_pricing_audit_logs')->latest('created_at')->take(10)->get();

        return view('admin.utilities.index', compact(
            'transactions',
            'q',
            'type',
            'status',
            'period',
            'dateFrom',
            'dateTo',
            'totalVolumeNgn',
            'totalCustomerChargedNgn',
            'totalProviderCostNgn',
            'totalRealizedProfitNgn',
            'totalRealizedProfitUsd',
            'estimatedPendingProfitNgn',
            'dataProfitNgn',
            'airtimeProfitNgn',
            'electricityProfitNgn',
            'bettingProfitNgn',
            'totalSuccessful',
            'totalFailed',
            'totalRefunded',
            'totalPending',
            'apiToken',
            'pin',
            'baseUrl',
            'simulate',
            'pricingSettings',
            'providerBalance',
            'auditLogs'
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
     * Save BigiSub and utility pricing configuration settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            // Gateway
            'bigisub_api_token' => 'sometimes|nullable|string|max:255',
            'bigisub_pin' => 'sometimes|nullable|string|max:10',
            'bigisub_base_url' => 'sometimes|nullable|url|max:255',
            'bigisub_simulate' => 'sometimes|nullable|in:0,1',

            // Data
            'utility_data_enabled' => 'sometimes|nullable|in:0,1',
            'utility_data_pricing_mode' => 'sometimes|required|in:fixed,percent',
            'utility_data_markup_value' => 'sometimes|required|numeric|min:0|max:10000',
            'utility_data_min_amount' => 'sometimes|required|numeric|min:1',
            'utility_data_max_amount' => 'sometimes|required|numeric|min:1',

            // Airtime
            'utility_airtime_enabled' => 'sometimes|nullable|in:0,1',
            'utility_airtime_discount_percent' => 'sometimes|required|numeric|min:0|max:20',
            'utility_airtime_fee_mode' => 'sometimes|required|in:none,fixed,percent',
            'utility_airtime_fee_value' => 'sometimes|required|numeric|min:0',
            'utility_airtime_provider_discount_percent' => 'sometimes|required|numeric|min:0|max:20',
            'utility_airtime_min_amount' => 'sometimes|required|numeric|min:1',
            'utility_airtime_max_amount' => 'sometimes|required|numeric|min:1',

            // Electricity
            'utility_electricity_enabled' => 'sometimes|nullable|in:0,1',
            'utility_electricity_fee_mode' => 'sometimes|required|in:fixed,percent,none',
            'utility_electricity_fee_value' => 'sometimes|required|numeric|min:0',
            'utility_electricity_min_amount' => 'sometimes|required|numeric|min:1',
            'utility_electricity_max_amount' => 'sometimes|required|numeric|min:1',

            // Betting
            'utility_betting_enabled' => 'sometimes|nullable|in:0,1',
            'utility_betting_fee_mode' => 'sometimes|required|in:percent,fixed,none',
            'utility_betting_fee_value' => 'sometimes|required|numeric|min:0',
            'utility_betting_min_amount' => 'sometimes|required|numeric|min:1',
            'utility_betting_max_amount' => 'sometimes|required|numeric|min:1',

            // Safety
            'utility_allow_negative_profit' => 'sometimes|nullable|in:0,1',
        ]);

        // 1. Gateway settings
        if ($request->has('bigisub_api_token')) {
            Setting::updateOrCreate(
                ['key' => 'bigisub_api_token'],
                ['value' => trim((string) $request->input('bigisub_api_token', ''))]
            );
        }

        if ($request->has('bigisub_pin')) {
            Setting::updateOrCreate(
                ['key' => 'bigisub_pin'],
                ['value' => trim((string) $request->input('bigisub_pin', ''))]
            );
        }

        if ($request->has('bigisub_base_url')) {
            Setting::updateOrCreate(
                ['key' => 'bigisub_base_url'],
                ['value' => rtrim(trim((string) $request->input('bigisub_base_url')), '/')]
            );
        }

        if ($request->has('bigisub_simulate')) {
            Setting::updateOrCreate(
                ['key' => 'bigisub_simulate'],
                ['value' => $request->input('bigisub_simulate') == '1' ? '1' : '0']
            );
        }

        // 2. Pass pricing inputs through UtilityPricingService with audit logging
        $existing = $this->utilityService->getPricingService()->getAllSettings();
        $pricingInputs = [];

        // Check if full settings form was submitted (by checking presence of mode/inputs)
        $hasDataSection = $request->has('utility_data_pricing_mode');
        $hasAirtimeSection = $request->has('utility_airtime_discount_percent');
        $hasElectricitySection = $request->has('utility_electricity_fee_mode');
        $hasBettingSection = $request->has('utility_betting_fee_mode');

        if ($hasDataSection) {
            $pricingInputs['utility_data_enabled'] = $request->has('utility_data_enabled') ? '1' : '0';
            $pricingInputs['utility_data_pricing_mode'] = (string) $request->input('utility_data_pricing_mode', 'fixed');
            $pricingInputs['utility_data_markup_value'] = (string) $request->input('utility_data_markup_value', '40.0');
            $pricingInputs['utility_data_min_amount'] = (string) $request->input('utility_data_min_amount', '50.0');
            $pricingInputs['utility_data_max_amount'] = (string) $request->input('utility_data_max_amount', '100000.0');
        }

        if ($hasAirtimeSection) {
            $pricingInputs['utility_airtime_enabled'] = $request->has('utility_airtime_enabled') ? '1' : '0';
            $pricingInputs['utility_airtime_discount_percent'] = (string) $request->input('utility_airtime_discount_percent', '0.0');
            $pricingInputs['utility_airtime_fee_mode'] = (string) $request->input('utility_airtime_fee_mode', 'none');
            $pricingInputs['utility_airtime_fee_value'] = (string) $request->input('utility_airtime_fee_value', '0.0');
            $pricingInputs['utility_airtime_provider_discount_percent'] = (string) $request->input('utility_airtime_provider_discount_percent', '2.0');
            $pricingInputs['utility_airtime_min_amount'] = (string) $request->input('utility_airtime_min_amount', '50.0');
            $pricingInputs['utility_airtime_max_amount'] = (string) $request->input('utility_airtime_max_amount', '50000.0');
        }

        if ($hasElectricitySection) {
            $pricingInputs['utility_electricity_enabled'] = $request->has('utility_electricity_enabled') ? '1' : '0';
            $pricingInputs['utility_electricity_fee_mode'] = (string) $request->input('utility_electricity_fee_mode', 'fixed');
            $pricingInputs['utility_electricity_fee_value'] = (string) $request->input('utility_electricity_fee_value', '100.0');
            $pricingInputs['utility_electricity_min_amount'] = (string) $request->input('utility_electricity_min_amount', '500.0');
            $pricingInputs['utility_electricity_max_amount'] = (string) $request->input('utility_electricity_max_amount', '500000.0');
        }

        if ($hasBettingSection) {
            $pricingInputs['utility_betting_enabled'] = $request->has('utility_betting_enabled') ? '1' : '0';
            $pricingInputs['utility_betting_fee_mode'] = (string) $request->input('utility_betting_fee_mode', 'percent');
            $pricingInputs['utility_betting_fee_value'] = (string) $request->input('utility_betting_fee_value', '1.5');
            $pricingInputs['utility_betting_min_amount'] = (string) $request->input('utility_betting_min_amount', '100.0');
            $pricingInputs['utility_betting_max_amount'] = (string) $request->input('utility_betting_max_amount', '500000.0');
        }

        if ($hasAirtimeSection || $request->has('utility_allow_negative_profit')) {
            $pricingInputs['utility_allow_negative_profit'] = $request->has('utility_allow_negative_profit') ? '1' : '0';
        }

        // Also permit individual key updates if posted outside sections
        foreach ($existing as $k => $v) {
            if ($request->has($k) && !isset($pricingInputs[$k])) {
                $pricingInputs[$k] = (string) $request->input($k);
            }
        }

        $admin = auth()->user();
        $this->utilityService->getPricingService()->updateSettings(
            $pricingInputs,
            $admin?->id,
            $admin?->name,
            $admin?->email,
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Utility pricing rules, markup, and gateway settings saved successfully.');
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
