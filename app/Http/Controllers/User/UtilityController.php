<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\UtilityTransaction;
use App\Services\BigiSubService;
use App\Services\UtilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class UtilityController extends Controller
{
    protected UtilityService $utilityService;
    protected BigiSubService $bigiSubService;

    public function __construct(UtilityService $utilityService, BigiSubService $bigiSubService)
    {
        $this->utilityService = $utilityService;
        $this->bigiSubService = $bigiSubService;
    }

    /**
     * Unified Utilities Hub / Airtime & Data Page.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $rate = $this->utilityService->getExchangeRate();
        $balanceNgn = $this->utilityService->convertUsdToNgn((float) $user->walletBalance);

        $recentTransactions = UtilityTransaction::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $activeTab = $request->query('tab', 'airtime');

        $mtnPlans = $this->bigiSubService->getDataPlans('mtn');
        $airtelPlans = $this->bigiSubService->getDataPlans('airtel');
        $gloPlans = $this->bigiSubService->getDataPlans('glo');
        $nineMobilePlans = $this->bigiSubService->getDataPlans('9mobile');

        $supportedDiscos = $this->bigiSubService->getSupportedDiscos();
        $supportedBetting = $this->bigiSubService->getSupportedBettingPlatforms();

        $airtimeDiscount = (float) (Setting::where('key', 'utility_airtime_discount_percent')->value('value') ?? 2.0);
        $serviceStatuses = app(\App\Services\ServiceManager::class)->getAllStatuses();

        return view('user.utilities.index', compact(
            'user',
            'rate',
            'balanceNgn',
            'recentTransactions',
            'activeTab',
            'mtnPlans',
            'airtelPlans',
            'gloPlans',
            'nineMobilePlans',
            'supportedDiscos',
            'supportedBetting',
            'airtimeDiscount',
            'serviceStatuses'
        ));
    }

    /**
     * Dedicated Airtime View.
     */
    public function airtime(Request $request)
    {
        $request->merge(['tab' => 'airtime']);
        return $this->index($request);
    }

    /**
     * Dedicated Data Bundle View.
     */
    public function data(Request $request)
    {
        $request->merge(['tab' => 'data']);
        return $this->index($request);
    }

    /**
     * Dedicated Electricity View.
     */
    public function electricity(Request $request)
    {
        $request->merge(['tab' => 'electricity']);
        return $this->index($request);
    }

    /**
     * Dedicated Betting View.
     */
    public function betting(Request $request)
    {
        $request->merge(['tab' => 'betting']);
        return $this->index($request);
    }

    /**
     * AJAX: Get dynamic plans for selected network.
     */
    public function getPlans(Request $request): JsonResponse
    {
        $network = strtolower((string) $request->query('network', 'mtn'));
        $plans = $this->bigiSubService->getDataPlans($network);

        return response()->json([
            'success' => true,
            'network' => $network,
            'plans' => $plans,
        ]);
    }

    /**
     * AJAX: Verify Electricity Meter.
     */
    public function verifyMeter(Request $request): JsonResponse
    {
        $serviceManager = app(\App\Services\ServiceManager::class);
        if (!$serviceManager->isAvailable('electricity')) {
            return response()->json([
                'success' => false,
                'message' => $serviceManager->getMaintenanceMessage('electricity'),
            ], 403);
        }

        $request->validate([
            'disco' => 'required|string',
            'meter_number' => 'required|string|min:6|max:20',
            'meter_type' => 'required|in:prepaid,postpaid',
        ]);

        $res = $this->bigiSubService->verifyMeter(
            $request->input('disco'),
            $request->input('meter_number'),
            $request->input('meter_type')
        );

        if (!$res['success']) {
            return response()->json([
                'success' => false,
                'message' => $res['message'] ?? 'Unable to verify meter number. Please check the details and retry.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'customer_name' => $res['customer_name'],
            'name' => $res['customer_name'],
            'address' => $res['address'] ?? null,
            'meter_number' => $res['meter_number'],
            'disco' => $res['disco'],
            'meter_type' => $res['meter_type'],
        ]);
    }

    /**
     * AJAX: Verify Betting Account ID.
     */
    public function verifyBetting(Request $request): JsonResponse
    {
        $serviceManager = app(\App\Services\ServiceManager::class);
        if (!$serviceManager->isAvailable('betting')) {
            return response()->json([
                'success' => false,
                'message' => $serviceManager->getMaintenanceMessage('betting'),
            ], 403);
        }

        $request->validate([
            'platform' => 'required|string',
            'customer_id' => 'required|string|min:3|max:50',
        ]);

        $res = $this->bigiSubService->verifyBettingCustomer(
            $request->input('platform'),
            $request->input('customer_id')
        );

        if (!$res['success']) {
            return response()->json([
                'success' => false,
                'message' => $res['message'] ?? 'Unable to verify betting account ID.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'customer_name' => $res['customer_name'],
            'name' => $res['customer_name'],
            'customer_id' => $res['customer_id'],
            'platform' => $res['platform'],
        ]);
    }

    /**
     * POST: Purchase Airtime.
     */
    public function purchaseAirtime(Request $request)
    {
        $serviceManager = app(\App\Services\ServiceManager::class);
        if (!$serviceManager->isAvailable('airtime')) {
            $msg = $serviceManager->getMaintenanceMessage('airtime');
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        if (!$request->has('phone_number') && $request->has('phone')) {
            $request->merge(['phone_number' => $request->input('phone')]);
        }

        $request->validate([
            'network' => 'required|in:mtn,airtel,glo,9mobile',
            'phone_number' => 'required|string|min:10|max:14',
            'amount' => 'required|numeric|min:50|max:50000',
        ]);

        try {
            $tx = $this->utilityService->processAirtimePurchase(
                auth()->user(),
                $request->all(),
                $request->ip(),
                $request->userAgent()
            );

            if ($request->expectsJson() || $request->ajax()) {
                if ($tx->isCompleted()) {
                    return response()->json([
                        'success' => true,
                        'status' => 'completed',
                        'reference' => $tx->reference,
                        'receipt_url' => route('utilities.receipt', $tx->reference),
                    ]);
                }
                if ($tx->isUncertain()) {
                    return response()->json([
                        'success' => true,
                        'status' => 'uncertain',
                        'reference' => $tx->reference,
                        'receipt_url' => route('utilities.receipt', $tx->reference),
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'status' => 'failed',
                    'message' => $tx->error_message ?? 'Airtime purchase failed. Your wallet was refunded.',
                ], 422);
            }

            if ($tx->isCompleted()) {
                return redirect()->route('utilities.receipt', $tx->reference)
                    ->with('success', "Airtime of ₦" . number_format($tx->amount_ngn, 2) . " to {$tx->customer_target} was successful!");
            }

            if ($tx->isUncertain()) {
                return redirect()->route('utilities.receipt', $tx->reference)
                    ->with('warning', 'Your airtime request was submitted and is pending final network confirmation.');
            }

            return back()->with('error', $tx->error_message ?? 'Airtime purchase failed. Your wallet was refunded.');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * POST: Purchase Data Bundle.
     */
    public function purchaseData(Request $request)
    {
        $serviceManager = app(\App\Services\ServiceManager::class);
        if (!$serviceManager->isAvailable('data')) {
            $msg = $serviceManager->getMaintenanceMessage('data');
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        if (!$request->has('phone_number') && $request->has('phone')) {
            $request->merge(['phone_number' => $request->input('phone')]);
        }

        $request->validate([
            'network' => 'required|in:mtn,airtel,glo,9mobile',
            'phone_number' => 'required|string|min:10|max:14',
            'plan_id' => 'required',
        ]);

        try {
            $tx = $this->utilityService->processDataPurchase(
                auth()->user(),
                $request->all(),
                $request->ip(),
                $request->userAgent()
            );

            if ($request->expectsJson() || $request->ajax()) {
                if ($tx->isCompleted()) {
                    return response()->json([
                        'success' => true,
                        'status' => 'completed',
                        'reference' => $tx->reference,
                        'receipt_url' => route('utilities.receipt', $tx->reference),
                    ]);
                }
                if ($tx->isUncertain()) {
                    return response()->json([
                        'success' => true,
                        'status' => 'uncertain',
                        'reference' => $tx->reference,
                        'receipt_url' => route('utilities.receipt', $tx->reference),
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'status' => 'failed',
                    'message' => $tx->error_message ?? 'Data purchase failed. Your wallet was refunded.',
                ], 422);
            }

            if ($tx->isCompleted()) {
                return redirect()->route('utilities.receipt', $tx->reference)
                    ->with('success', "Data bundle ({$tx->product_name}) for {$tx->customer_target} was delivered successfully!");
            }

            if ($tx->isUncertain()) {
                return redirect()->route('utilities.receipt', $tx->reference)
                    ->with('warning', 'Data purchase submitted and is awaiting delivery confirmation.');
            }

            return back()->with('error', $tx->error_message ?? 'Data purchase failed. Your wallet was refunded.');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * POST: Pay Electricity Bill.
     */
    public function payElectricity(Request $request)
    {
        $serviceManager = app(\App\Services\ServiceManager::class);
        if (!$serviceManager->isAvailable('electricity')) {
            $msg = $serviceManager->getMaintenanceMessage('electricity');
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        $request->validate([
            'disco' => 'required|string',
            'meter_number' => 'required|string|min:6|max:20',
            'meter_type' => 'required|in:prepaid,postpaid',
            'customer_name' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'amount' => 'required|numeric|min:500|max:500000',
        ]);

        try {
            $tx = $this->utilityService->processElectricityPayment(
                auth()->user(),
                $request->all(),
                $request->ip(),
                $request->userAgent()
            );

            if ($request->expectsJson() || $request->ajax()) {
                if ($tx->isCompleted()) {
                    return response()->json([
                        'success' => true,
                        'status' => 'completed',
                        'reference' => $tx->reference,
                        'token' => $tx->token,
                        'units' => $tx->units,
                        'receipt_url' => route('utilities.receipt', $tx->reference),
                    ]);
                }
                if ($tx->isUncertain()) {
                    return response()->json([
                        'success' => true,
                        'status' => 'uncertain',
                        'reference' => $tx->reference,
                        'receipt_url' => route('utilities.receipt', $tx->reference),
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'status' => 'failed',
                    'message' => $tx->error_message ?? 'Electricity payment failed. Your wallet was refunded.',
                ], 422);
            }

            if ($tx->isCompleted()) {
                $msg = "Electricity bill payment of ₦" . number_format($tx->amount_ngn, 2) . " was successful!";
                if ($tx->token) {
                    $msg .= " Your prepaid token is {$tx->token}";
                }
                return redirect()->route('utilities.receipt', $tx->reference)->with('success', $msg);
            }

            if ($tx->isUncertain()) {
                return redirect()->route('utilities.receipt', $tx->reference)
                    ->with('warning', 'Electricity payment is processing with the power company.');
            }

            return back()->with('error', $tx->error_message ?? 'Electricity payment failed. Your wallet was refunded.');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * POST: Fund Betting Wallet.
     */
    public function fundBetting(Request $request)
    {
        $serviceManager = app(\App\Services\ServiceManager::class);
        if (!$serviceManager->isAvailable('betting')) {
            $msg = $serviceManager->getMaintenanceMessage('betting');
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        $request->validate([
            'platform' => 'required|string',
            'customer_id' => 'required|string|min:3|max:50',
            'customer_name' => 'nullable|string',
            'amount' => 'required|numeric|min:100|max:500000',
        ]);

        try {
            $tx = $this->utilityService->processBettingFunding(
                auth()->user(),
                $request->all(),
                $request->ip(),
                $request->userAgent()
            );

            if ($request->expectsJson() || $request->ajax()) {
                if ($tx->isCompleted()) {
                    return response()->json([
                        'success' => true,
                        'status' => 'completed',
                        'reference' => $tx->reference,
                        'receipt_url' => route('utilities.receipt', $tx->reference),
                    ]);
                }
                if ($tx->isUncertain()) {
                    return response()->json([
                        'success' => true,
                        'status' => 'uncertain',
                        'reference' => $tx->reference,
                        'receipt_url' => route('utilities.receipt', $tx->reference),
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'status' => 'failed',
                    'message' => $tx->error_message ?? 'Betting funding failed. Your wallet was refunded.',
                ], 422);
            }

            if ($tx->isCompleted()) {
                return redirect()->route('utilities.receipt', $tx->reference)
                    ->with('success', "Betting account for {$tx->customer_target} was funded with ₦" . number_format($tx->amount_ngn, 2) . " successfully!");
            }

            if ($tx->isUncertain()) {
                return redirect()->route('utilities.receipt', $tx->reference)
                    ->with('warning', 'Betting funding is currently processing with the gaming operator.');
            }

            return back()->with('error', $tx->error_message ?? 'Betting funding failed. Your wallet was refunded.');
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Transaction History.
     */
    public function history(Request $request)
    {
        $user = auth()->user();
        $type = $request->query('type');
        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));

        $query = UtilityTransaction::where('user_id', $user->id)->latest();

        if ($type) {
            $query->where('service_type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($q !== '') {
            $query->where(function ($sq) use ($q) {
                $sq->where('reference', 'like', "%{$q}%")
                    ->orWhere('customer_target', 'like', "%{$q}%")
                    ->orWhere('product_name', 'like', "%{$q}%")
                    ->orWhere('token', 'like', "%{$q}%");
            });
        }

        $transactions = $query->paginate(15)->withQueryString();

        return view('user.utilities.history', compact('transactions', 'type', 'status', 'q'));
    }

    /**
     * Transaction Receipt.
     */
    public function receipt(string $reference)
    {
        $user = auth()->user();
        $tx = UtilityTransaction::where('reference', $reference)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return view('user.utilities.receipt', compact('tx'));
    }
}
