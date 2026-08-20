<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SportyBetCode;
use App\Models\Setting;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SportyBetCodeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $codes = SportyBetCode::where('is_active', true)
            ->latest()
            ->paginate(12);

        // Get purchased codes for the user
        $purchasedCodeIds = $user->sportyBetCodes()->pluck('sporty_bet_codes.id')->toArray();

        return view('user.sporty-bet-codes', compact('codes', 'purchasedCodeIds'));
    }

    public function purchase(SportyBetCode $code, WalletService $wallet)
    {
        $user = auth()->user();

        // Check if user already purchased this code
        if ($user->sportyBetCodes()->where('sporty_bet_code_id', $code->id)->exists()) {
            return back()->with('error', 'You already have access to this code.');
        }

        // If it's a free code, just attach it
        if ($code->amount == 0) {
            $user->sportyBetCodes()->attach($code->id, ['amount_paid' => 0]);
            return back()->with('success', 'Free code unlocked!');
        }

        // Get exchange rate from settings (NGN per USD)
        $ngnPerUsd = (float) (Setting::where('key', 'ngn_per_usd')->value('value') ?? 1500);
        if ($ngnPerUsd <= 0) {
            $ngnPerUsd = 1500;
        }

        // Convert NGN amount to USD (since wallet is in USD)
        $amountUsd = $ngnPerUsd > 0 ? ($code->amount / $ngnPerUsd) : 0;
        $amountUsd = round($amountUsd, 2);

        // Check if user has enough balance
        if ($user->walletBalance < $amountUsd) {
            return back()->with('error', 'Insufficient wallet balance.');
        }

        DB::transaction(function () use ($user, $code, $wallet, $amountUsd) {
            // Attach the code, store amount paid in NGN (original amount)
            $user->sportyBetCodes()->attach($code->id, ['amount_paid' => $code->amount]);

            // Deduct from wallet using WalletService (in USD)
            if ($amountUsd > 0) {
                $wallet->debit($user, $amountUsd, 'sportybet_debit', 'SPORTYBETCODE-' . $code->id, [
                    'sporty_bet_code_id' => $code->id,
                    'ngn_amount' => $code->amount,
                ]);
            }
        });

        return back()->with('success', 'Code purchased successfully!');
    }
}
