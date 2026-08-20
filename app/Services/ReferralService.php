<?php

namespace App\Services;

use App\Models\FundingTransaction;
use App\Models\User;
use App\Models\WalletTransaction;

class ReferralService
{
    public function awardFirstFundingBonus(FundingTransaction $fundingTx): void
    {
        $user = $fundingTx->user;
        if (! $user || ! $user->referred_by) {
            return;
        }

        $alreadyPaid = WalletTransaction::where('type', 'referral_bonus')
            ->where('reference', 'REFBONUS-'.(string) $fundingTx->id)
            ->exists();
        if ($alreadyPaid) {
            return;
        }

        $fundingCreditsCount = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'funding_credit')
            ->count();
        if ($fundingCreditsCount > 1) {
            return;
        }

        $referrer = User::find($user->referred_by);
        if (! $referrer) {
            return;
        }

        $bonus = round(((float) $fundingTx->amount_usd) * 0.05, 2);
        if ($bonus <= 0) {
            return;
        }

        app(WalletService::class)->credit($referrer, $bonus, 'referral_bonus', 'REFBONUS-'.(string) $fundingTx->id, [
            'referred_user_id' => $user->id,
            'funding_transaction_id' => $fundingTx->id,
        ]);
    }
}
