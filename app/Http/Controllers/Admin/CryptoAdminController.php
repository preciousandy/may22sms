<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CryptoTransaction;
use App\Models\CryptoWebhook;
use App\Models\ServiceConfiguration;
use App\Models\Setting;
use App\Services\QuidaxService;
use App\Services\ServiceManager;
use Illuminate\Http\Request;

class CryptoAdminController extends Controller
{
    protected QuidaxService $quidax;
    protected ServiceManager $serviceManager;

    public function __construct(QuidaxService $quidax, ServiceManager $serviceManager)
    {
        $this->quidax = $quidax;
        $this->serviceManager = $serviceManager;
    }

    /**
     * Admin Crypto Dashboard Overview.
     */
    public function index(Request $request)
    {
        // Service configuration
        $serviceConfig = ServiceConfiguration::where('key', 'crypto_to_naira')->first();
        $isAvailable = $serviceConfig ? (bool) $serviceConfig->is_enabled : false;
        $isTestMode = $this->quidax->isTestMode();

        // Volume and KPI Statistics
        $totalTransactionsCount = CryptoTransaction::count();
        $completedTransactionsCount = CryptoTransaction::where('status', 'completed')->count();
        $pendingTransactionsCount = CryptoTransaction::whereIn('status', ['pending', 'address_assigned'])->count();
        $processingTransactionsCount = CryptoTransaction::where('status', 'processing')->count();
        $attentionTransactionsCount = CryptoTransaction::whereIn('status', ['needs_attention', 'failed'])->count();

        $totalUsdtVolume = (float) CryptoTransaction::where('status', 'completed')->where('from_currency', 'usdt')->sum('from_amount');
        $totalNgnSettled = (float) CryptoTransaction::where('status', 'completed')->sum('to_amount');

        // Filter and Search Transactions Query
        $query = CryptoTransaction::with('user')->latest();

        if ($request->filled('status') && $request->query('status') !== 'all') {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('currency') && $request->query('currency') !== 'all') {
            $query->where('from_currency', strtolower($request->query('currency')));
        }

        if ($request->filled('q')) {
            $q = trim($request->query('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('merchant_reference', 'like', "%{$q}%")
                    ->orWhere('provider_reference', 'like', "%{$q}%")
                    ->orWhere('provider_public_id', 'like', "%{$q}%")
                    ->orWhere('account_number', 'like', "%{$q}%")
                    ->orWhere('deposit_address', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('email', 'like', "%{$q}%")
                          ->orWhere('name', 'like', "%{$q}%");
                    });
            });
        }

        $transactions = $query->paginate(20)->withQueryString();

        // Recent Webhooks for Audit / Debugging
        $recentWebhooks = CryptoWebhook::latest()->take(10)->get();

        return view('admin.crypto.index', compact(
            'serviceConfig',
            'isAvailable',
            'isTestMode',
            'totalTransactionsCount',
            'completedTransactionsCount',
            'pendingTransactionsCount',
            'processingTransactionsCount',
            'attentionTransactionsCount',
            'totalUsdtVolume',
            'totalNgnSettled',
            'transactions',
            'recentWebhooks'
        ));
    }

    /**
     * Manual Requery and Reconciliation with Quidax.
     */
    public function reconcile(Request $request, $id)
    {
        $tx = CryptoTransaction::findOrFail($id);

        try {
            $res = $this->quidax->requeryOffRamp($tx);

            return back()->with('success', "Transaction {$tx->merchant_reference} successfully reconciled with Quidax. Current status: {$tx->fresh()->status}.");
        } catch (\Throwable $e) {
            return back()->with('error', "Reconciliation failed: " . $e->getMessage());
        }
    }

    /**
     * Toggle Crypto Service Status (Enable / Disable).
     */
    public function toggleService(Request $request)
    {
        $current = $this->serviceManager->isAvailable('crypto_to_naira');
        $this->serviceManager->updateStatus(
            'crypto_to_naira',
            ! $current,
            'Toggled via Crypto Off-Ramp Management',
            null,
            auth()->user(),
            $request->ip()
        );

        $status = ! $current ? 'enabled' : 'disabled';

        return back()->with('success', "Crypto to Naira service has been {$status}.");
    }
}
