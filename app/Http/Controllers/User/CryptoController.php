<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CryptoTransaction;
use App\Services\QuidaxService;
use App\Services\ServiceManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CryptoController extends Controller
{
    protected QuidaxService $quidax;
    protected ServiceManager $serviceManager;

    public function __construct(QuidaxService $quidax, ServiceManager $serviceManager)
    {
        $this->quidax = $quidax;
        $this->serviceManager = $serviceManager;
    }

    /**
     * Display Crypto-to-Naira Dashboard.
     */
    public function index(Request $request)
    {
        $isAvailable = $this->serviceManager->isAvailable('crypto_to_naira');
        $maintenanceMessage = $this->serviceManager->getMaintenanceMessage('crypto_to_naira');
        $isTestMode = $this->quidax->isTestMode();

        $user = auth()->user();

        // Get supported currencies from config
        $supportedCurrencies = config('quidax.supported_currencies', []);

        // Get live tickers
        $usdtRate = $this->quidax->getMarketTicker('usdtngn');
        $btcRate = $this->quidax->getMarketTicker('btcngn');

        // Fetch supported Nigerian banks
        $banks = $this->quidax->getSupportedBanks();

        // User's recent crypto transactions
        $transactions = CryptoTransaction::where('user_id', $user->id)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Active transaction if reference provided in query
        $activeTx = null;
        if ($request->filled('ref')) {
            $activeTx = CryptoTransaction::where('user_id', $user->id)
                ->where('merchant_reference', $request->query('ref'))
                ->first();
        }

        return view('user.crypto.index', compact(
            'isAvailable',
            'maintenanceMessage',
            'isTestMode',
            'supportedCurrencies',
            'usdtRate',
            'btcRate',
            'banks',
            'transactions',
            'activeTx'
        ));
    }

    /**
     * AJAX endpoint: Calculate real-time conversion quote.
     */
    public function quote(Request $request)
    {
        $validated = $request->validate([
            'currency' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0.00000001'],
            'network' => ['required', 'string'],
        ]);

        try {
            $quote = $this->quidax->calculateQuote(
                $validated['currency'],
                (float) $validated['amount'],
                $validated['network']
            );

            return response()->json([
                'status' => 'success',
                'quote' => $quote,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Create an off-ramp conversion transaction and generate deposit address.
     */
    public function create(Request $request)
    {
        $this->serviceManager->ensureAvailable('crypto_to_naira');

        $validated = $request->validate([
            'currency' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0.00000001'],
            'network' => ['required', 'string'],
            'payout_destination' => ['required', 'in:bank_account,wallet'],
            'bank_code' => ['nullable', 'required_if:payout_destination,bank_account', 'string'],
            'account_number' => ['nullable', 'required_if:payout_destination,bank_account', 'string', 'digits_between:9,12'],
            'account_name' => ['nullable', 'string', 'max:150'],
        ]);

        try {
            $user = auth()->user();

            // 1. Initiate off-ramp transaction
            $tx = $this->quidax->initiateOffRamp(
                $user,
                $validated['currency'],
                (float) $validated['amount'],
                $validated['network'],
                $validated['payout_destination'],
                $validated['bank_code'] ?? null,
                $validated['account_number'] ?? null,
                $validated['account_name'] ?? null
            );

            // 2. Attach bank account if direct bank payout
            if ($tx->payout_destination === 'bank_account' && $tx->bank_code && $tx->account_number) {
                $this->quidax->attachBankAccount($tx, $tx->bank_code, $tx->account_number, $tx->account_name);
            }

            // 3. Confirm transaction to generate deposit address
            $this->quidax->confirmOffRamp($tx);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Deposit address successfully generated.',
                    'transaction' => $tx->fresh(),
                    'redirect_url' => "/services/crypto-to-naira?ref={$tx->merchant_reference}",
                ]);
            }

            return redirect("/services/crypto-to-naira?ref={$tx->merchant_reference}")
                ->with('success', 'Deposit address generated! Please send the exact cryptocurrency to complete your conversion.');
        } catch (\Throwable $e) {
            Log::error('Crypto off-ramp creation error: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * AJAX endpoint: Query status of a transaction.
     */
    public function status(Request $request, $reference)
    {
        $tx = CryptoTransaction::where('user_id', auth()->id())
            ->where('merchant_reference', $reference)
            ->firstOrFail();

        // Optional status sync with Quidax if not in final state
        if (! in_array($tx->status, ['completed', 'failed'], true)) {
            try {
                $this->quidax->requeryOffRamp($tx);
            } catch (\Throwable $e) {
                // non-fatal, return current DB state
            }
        }

        $fresh = $tx->fresh();

        return response()->json([
            'status' => 'success',
            'data' => [
                'merchant_reference' => $fresh->merchant_reference,
                'status' => $fresh->status,
                'from_currency' => strtoupper($fresh->from_currency),
                'from_amount' => (float) $fresh->from_amount,
                'to_amount' => (float) $fresh->to_amount,
                'network' => strtoupper($fresh->network),
                'deposit_address' => $fresh->deposit_address,
                'deposit_tag' => $fresh->deposit_tag,
                'payout_destination' => $fresh->payout_destination,
                'bank_name' => $fresh->bank_name,
                'account_number' => $fresh->account_number,
                'crypto_received_amount' => $fresh->crypto_received_amount,
                'crypto_txid' => $fresh->crypto_txid,
                'wallet_credited' => $fresh->wallet_credited,
                'settled_at' => $fresh->settled_at?->format('M d, Y · h:i A'),
                'created_at' => $fresh->created_at->format('M d, Y · h:i A'),
            ],
        ]);
    }
}
